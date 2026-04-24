<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Facades\JWTAuth;


class AtelierController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog
    ) {}


    public function liste(Request $request)
    {
        $user = $this->optionalJwtUser($request);

        $formations = Formation::query()
            ->with(['formateur', 'categorie'])
            ->get();

        $rows = $formations->map(fn (Formation $f) => $this->mapFormationRow($f));


        if ($request->query('mine') === '1' && $user) {
            $uid = (int) $user->id;
            $rows = $rows->filter(fn (array $row) => (int) $row['idUtilisateur'] === $uid)->values();

            $this->activityLog->log(
                $user->id,
                $this->roleLabel($user),
                'click_mes_formations',
                []
            );
        }

        if ($request->filled('categorie')) {
            $categorie = htmlspecialchars(trim($request->query('categorie')), ENT_QUOTES, 'UTF-8');
            $rows = $rows->filter(fn (array $row) => $row['categorie'] === $categorie)->values();

            $this->activityLog->log(
                $user?->id,
                $this->roleLabel($user),
                'use_filter',
                ['type' => 'categorie', 'categorie' => $categorie]
            );
        }

        if ($request->filled('search')) {
            $search = htmlspecialchars(trim($request->query('search')), ENT_QUOTES, 'UTF-8');
            $searchLower = strtolower($search);
            $rows = $rows->filter(function (array $row) use ($searchLower) {
                return str_contains(strtolower($row['titre']), $searchLower)
                    || str_contains(strtolower((string) $row['description']), $searchLower)
                    || str_contains(strtolower((string) $row['formateur']), $searchLower);
            })->values();

            $this->activityLog->log(
                $user?->id,
                $this->roleLabel($user),
                'use_filter',
                ['type' => 'search', 'search' => $search]
            );
        }

        return response()->json([
            'liste_atelier' => $rows->all(),
            'count' => $rows->count(),
        ]);
    }

 
    public function detail(Request $request, int $id)
    {
        $formation = Formation::query()
            ->with(['formateur', 'categorie'])
            ->find($id);

        if (! $formation) {
            return response()->json(['message' => 'Formation introuvable'], 404);
        }

        $user = $this->optionalJwtUser($request);
        // MongoDB — historisation (consultation fiche formation)
        $this->activityLog->logEvent('course_view', [
            'user_id' => $user?->id,
            'role' => $this->roleLabel($user),
            'course_id' => $id,
        ]);

        $inscrits = 0;
        if (Schema::hasTable('inscription')) {
            $inscrits = (int) DB::table('inscription')
                ->where('idFormation', $formation->id)
                ->count();
        }

        $vues = (int) ($formation->vues ?? 0);
        $modules = [];
        if (Schema::hasTable('lecon')) {
            $rows = DB::table('lecon')
                ->where('idFormation', $formation->id)
                ->orderBy('id')
                ->pluck('titre')
                ->toArray();
            $modules = array_values(array_filter(array_map('strval', $rows)));
        }

        return response()->json(array_merge(
            $this->mapFormationRow($formation),
            [
            'apprenants' => $inscrits,
            'vues' => $vues,
            'modules' => $modules,
            ]
        ));
    }

   
    public function logActivity(Request $request)
    {
        $action = $request->input('action', '');
        $action = is_string($action) ? htmlspecialchars(trim($action), ENT_QUOTES, 'UTF-8') : '';

        if ($action === '') {
            return response()->json(['success' => false, 'message' => 'Action manquante'], 400);
        }

        $user = $this->optionalJwtUser($request);
        $metadata = $request->except('action');

        $this->activityLog->log(
            $user?->id,
            $this->roleLabel($user),
            $action,
            is_array($metadata) ? $metadata : []
        );

        return response()->json(['success' => true]);
    }

  
    public function activityLogs(Request $request)
    {
        $user = auth('api')->user();
        $limit = min(max((int) $request->query('limit', 20), 1), 100);

        $logs = $this->activityLog->getByUser((int) $user->id, $limit);

        return response()->json([
            'logs' => $logs,
            'mongo_available' => $this->activityLog->isAvailable(),
        ]);
    }

    
    public function inscrire(int $id)
    {
    $user = auth('api')->user();

    if (! $user || strtoupper((string) $user->role) !== 'APPRENANT') {
        return response()->json(['message' => 'Seuls les apprenants peuvent s inscrire.'], 403);
    }

    $inscriptionContext = $this->resolveInscriptionContext($id);
    if ($inscriptionContext['status'] !== null) {
        return response()->json(['message' => $inscriptionContext['message']], $inscriptionContext['status']);
    }

    /** @var Formation $formation */
    $formation = $inscriptionContext['formation'];

    // ── Vérification limite de 5 inscriptions actives ──
    $nombreInscriptions = DB::table('inscription')
        ->where('idUtilisateur', $user->id)
        ->where('statut', 'en-cours')
        ->count();

    if ($nombreInscriptions >= 5) {
        return response()->json([
            'message' => 'Vous avez atteint la limite de 5 formations simultanées. Veuillez terminer ou vous désinscrire d\'une formation avant de vous inscrire à une nouvelle.',
        ], 400);
    }

    $already = DB::table('inscription')
        ->where('idUtilisateur', $user->id)
        ->where('idFormation', $formation->id)
        ->exists();

    $status = 200;
    $message = 'Vous etes deja inscrit a cette formation.';

    if (! $already) {
        DB::table('inscription')->insert($this->buildInscriptionPayload((int) $user->id, (int) $formation->id));

        $this->activityLog->logEvent('course_enrollment', [
            'user_id' => $user->id,
            'course_id' => $formation->id,
        ]);

        $status = 201;
        $message = 'Inscription effectuee avec succes.';
    }

    return response()->json(['message' => $message], $status);
    }

    
    public function desinscrire(int $id)
    {
        $user = auth('api')->user();
        $status = 200;
        $message = '';

        if (! $user || strtoupper((string) $user->role) !== 'APPRENANT') {
            $status = 403;
            $message = 'Seuls les apprenants peuvent se desinscrire.';
        } elseif (! Schema::hasTable('inscription')) {
            $status = 500;
            $message = 'Table inscription indisponible';
        } else {
            $deleted = DB::table('inscription')
                ->where('idUtilisateur', $user->id)
                ->where('idFormation', $id)
                ->delete();

            if ($deleted === 0) {
                $status = 404;
                $message = 'Aucune inscription trouvee pour cette formation.';
            } else {
                $this->activityLog->logEvent('course_unenrollment', [
                    'user_id' => $user->id,
                    'course_id' => $id,
                ]);

                $message = 'Desinscription effectuee avec succes.';
            }
        }

        return response()->json(['message' => $message], $status);
    }

   
    public function mesInscriptions()
    {
        $user = auth('api')->user();

        if (! $user || strtoupper((string) $user->role) !== 'APPRENANT') {
            return response()->json(['message' => 'Acces reserve aux apprenants.'], 403);
        }

        if (! Schema::hasTable('inscription')) {
            return response()->json(['inscriptions' => []]);
        }

        $rows = DB::table('inscription as i')
            ->join('formation as f', 'f.id', '=', 'i.idFormation')
            ->leftJoin('categorieformation as c', 'c.id', '=', 'f.idCategorie')
            ->where('i.idUtilisateur', $user->id)
            ->select(
                'f.id',
                'f.titre',
                'f.description',
                'f.duree',
                'f.prix',
                'f.niveauRequis',
                'f.vues',
                'c.nomCategorie as categorie',
                'i.statut',
                'i.dateInscription'
            )
            ->orderByDesc('i.id')
            ->get()
            ->map(function ($row) {
                $statutRaw = strtolower((string) ($row->statut ?? 'en-cours'));
                $statut = $statutRaw === 'termine' || $statutRaw === 'terminé' ? 'termine' : 'en-cours';
                $progression = $statut === 'termine' ? 100 : 35;

                return [
                    'id' => (int) $row->id,
                    'titre' => (string) $row->titre,
                    'description' => (string) ($row->description ?? ''),
                    'categorie' => (string) ($row->categorie ?? 'developpement'),
                    'duree' => (int) ($row->duree ?? 0),
                    'prix' => (float) ($row->prix ?? 0),
                    'statut' => $statut,
                    'dateInscription' => $row->dateInscription ?: now()->toDateString(),
                    'progression' => $progression,
                    'vues' => (int) ($row->vues ?? 0),
                    'niveau' => (string) ($row->niveauRequis ?? ''),
                ];
            })
            ->values();

        return response()->json(['inscriptions' => $rows]);
    }

    private function mapFormationRow(Formation $f): array
    {
        $formateur = $f->formateur;
        $nomFormateur = $formateur
            ? trim(($formateur->nom ?? '').' '.($formateur->prenom ?? ''))
            : '';
        $inscrits = 0;
        if (Schema::hasTable('inscription')) {
            $inscrits = (int) DB::table('inscription')
                ->where('idFormation', $f->id)
                ->count();
        }
        $vues = (int) ($f->vues ?? 0);

        return [
            'id' => $f->id,
            'titre' => $f->titre,
            'description' => $f->description,
            'categorie' => $f->categorie?->nomCategorie ?? '',
            'duree' => $f->duree,
            'prix' => $f->prix !== null ? (float) $f->prix : null,
            'formateur' => $nomFormateur,
            'niveau' => $f->niveauRequis,
            'idUtilisateur' => $f->idUtilisateur,
            'apprenants' => $inscrits,
            'vues' => $vues,
        ];
    }

    private function resolveInscriptionContext(int $id): array
    {
        $formation = Formation::find($id);
        if (! $formation) {
            return [
                'formation' => null,
                'status' => 404,
                'message' => 'Formation introuvable',
            ];
        }

        if (! Schema::hasTable('inscription')) {
            return [
                'formation' => null,
                'status' => 500,
                'message' => 'Table inscription indisponible',
            ];
        }

        return [
            'formation' => $formation,
            'status' => null,
            'message' => null,
        ];
    }

    private function buildInscriptionPayload(int $userId, int $formationId): array
    {
        $payload = [
            'idUtilisateur' => $userId,
            'idFormation' => $formationId,
        ];

        if (Schema::hasColumn('inscription', 'dateInscription')) {
            $payload['dateInscription'] = now()->toDateString();
        }
        if (Schema::hasColumn('inscription', 'statut')) {
            $payload['statut'] = 'en-cours';
        }
        if (Schema::hasColumn('inscription', 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn('inscription', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        return $payload;
    }

    private function optionalJwtUser(Request $request): ?\App\Models\User
    {
        if (! $request->bearerToken()) {
            return null;
        }

        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function roleLabel(?\App\Models\User $user): string
    {
        if ($user === null) {
            return 'guest';
        }

        return strtolower((string) $user->role);
    }

}
