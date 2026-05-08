<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function noter(Request $request, int $id)
    {
        $user = auth('api')->user();

        // Vérifier que l'utilisateur est apprenant
        if (!$user || strtoupper((string) $user->role) !== 'APPRENANT') {
            return response()->json(['message' => 'Accès réservé aux apprenants.'], 403);
        }

        // Vérifier que la formation existe
        $formation = Formation::find($id);
        if (!$formation) {
            return response()->json(['message' => 'Formation introuvable.'], 404);
        }

        // Vérifier que l'apprenant est inscrit
        $inscrit = DB::table('inscription')
            ->where('idUtilisateur', $user->id)
            ->where('idFormation', $id)
            ->exists();

        if (!$inscrit) {
            return response()->json(['message' => 'Vous devez être inscrit à la formation pour la noter.'], 403);
        }

        // Valider la note
        $request->validate([
            'note'        => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        // Vérifier si déjà noté
        $dejaNote = Rating::where('idUtilisateur', $user->id)
            ->where('idFormation', $id)
            ->exists();

        if ($dejaNote) {
            return response()->json(['message' => 'Vous avez déjà noté cette formation.'], 400);
        }

        $rating = Rating::create([
            'idUtilisateur' => $user->id,
            'idFormation'   => $id,
            'note'          => $request->note,
            'commentaire'   => $request->commentaire,
        ]);

        return response()->json($rating, 201);
    }
}