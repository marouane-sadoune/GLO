<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Delete $model, converting a foreign-key restriction into a clean 409
     * instead of letting the database exception bubble up as a 500.
     */
    protected function destroyOrConflict(Model $model): JsonResponse
    {
        try {
            $model->delete();
        } catch (QueryException) {
            return response()->json([
                'message' => 'Suppression impossible : cet enregistrement est référencé ailleurs.',
            ], 409);
        }

        return response()->json(null, 204);
    }
}
