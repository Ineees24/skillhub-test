<?php

namespace App\Http\Controllers;

use App\Models\Formation;
use App\Models\CategorieFormation;
use App\Models\Lecon;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog
    ) {}

    /**
     * @return array<string, mixed>
     */
    private function formationSnapshot(Formation $formation): array
    {
        return [
            'titre' => $formation->titre,
            'description' => $formation->description,
            'duree' => $formation->duree,
            'prix' => $formation->prix,
            'niveauRequis' => $formation->niveauRequis,
            'statut' => $formation->statut,
            'idCategorie' => $formation->idCategorie,
        ];
    }

    // GET /api/formations : formations du formateur connecté
    public function index()
    {
        $user = auth('api')->user();

        $formations = Formation::with('categorie')
            ->where('idUtilisateur', $user->id)
            ->get();

        return response()->json($formations);
    }

    // POST /api/formations : créer une formation
    public function store(Request $request)
    {
        $request->validate([
            'titre'        => 'required|string|max:150',
            'description'  => 'nullable|string',
            'duree'        => 'nullable|integer|min:1',
            'prix'         => 'nullable|numeric|min:0',
            'niveauRequis' => 'nullable|string|max:100',
            'statut'       => 'nullable|in:publié,brouillon',
            'idCategorie'  => 'nullable|exists:categorieformation,id',
        ]);

        $user = auth('api')->user();

        $formation = Formation::create([
            'titre'         => $request->titre,
            'description'   => $request->description,
            'duree'         => $request->duree,
            'prix'          => $request->prix,
            'niveauRequis'  => $request->niveauRequis,
            'statut'        => $request->statut ?? 'brouillon',
            'idUtilisateur' => $user->id,
            'idCategorie'   => $request->idCategorie,
        ]);

        $this->activityLog->logEvent('course_create', [
            'user_id' => $user->id,
            'course_id' => $formation->id,
            'data' => $this->formationSnapshot($formation),
        ]);

        return response()->json($formation, 201);
    }

    // PUT /api/formations/{id} : modifier une formation
    public function update(Request $request, $id)
    {
        $request->validate([
            'titre'        => 'sometimes|required|string|max:150',
            'description'  => 'nullable|string',
            'duree'        => 'nullable|integer|min:1',
            'prix'         => 'nullable|numeric|min:0',
            'niveauRequis' => 'nullable|string|max:100',
            'statut'       => 'nullable|in:publié,brouillon',
            'idCategorie'  => 'nullable|exists:categorieformation,id',
        ]);

        $user = auth('api')->user();

        $formation = Formation::where('id', $id)
            ->where('idUtilisateur', $user->id)
            ->firstOrFail();

        $before = $this->formationSnapshot($formation);

        $formation->update($request->only([
            'titre', 'description', 'duree', 'prix', 'niveauRequis', 'statut', 'idCategorie'
        ]));

        $formation->refresh();

        $this->activityLog->logEvent('course_update', [
            'course_id' => (int) $formation->id,
            'updated_by' => $user->id,
            'before' => $before,
            'after' => $this->formationSnapshot($formation),
        ]);

        return response()->json($formation->load('categorie'));
    }

    // DELETE /api/formations/{id} : supprimer une formation
    public function destroy($id)
    {
        $user = auth('api')->user();

        $formation = Formation::where('id', $id)
            ->where('idUtilisateur', $user->id)
            ->firstOrFail();

        $snapshot = $this->formationSnapshot($formation);
        $courseId = (int) $formation->id;

        $formation->delete();

        $this->activityLog->logEvent('course_delete', [
            'course_id' => $courseId,
            'deleted_by' => $user->id,
            'snapshot' => $snapshot,
        ]);

        return response()->json(['message' => 'Formation supprimée']);
    }

    // GET /api/categories
    public function categories()
    {
        return response()->json(CategorieFormation::all());
    }

    // GET /api/formations/{id}/modules : modules de la formation du formateur connecté
    public function modulesIndex($id)
    {
        $user = auth('api')->user();

        $formation = Formation::where('id', $id)
            ->where('idUtilisateur', $user->id)
            ->firstOrFail();

        $modules = Lecon::where('idFormation', $formation->id)
            ->orderBy('id')
            ->get();

        return response()->json($modules);
    }

    // POST /api/formations/{id}/modules : ajouter un module à la formation du formateur connecté
    public function modulesStore(Request $request, $id)
    {
        $request->validate([
            'titre' => 'required|string|max:150',
            'duree' => 'nullable|integer|min:1',
            'contenu' => 'nullable|string',
        ]);

        $user = auth('api')->user();

        $formation = Formation::where('id', $id)
            ->where('idUtilisateur', $user->id)
            ->firstOrFail();

        $module = Lecon::create([
            'titre' => $request->titre,
            'duree' => $request->duree,
            'contenu' => $request->contenu,
            'idFormation' => $formation->id,
        ]);

        $this->activityLog->logEvent('module_create', [
            'user_id' => $user->id,
            'course_id' => $formation->id,
            'module_id' => $module->id,
            'module' => [
                'titre' => $module->titre,
                'duree' => $module->duree,
                'contenu' => $module->contenu,
            ],
        ]);

        return response()->json($module, 201);
    }
}