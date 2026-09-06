<?php

use App\Http\Controllers\Api\V1\AssignmentRequestController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\EstablishmentController;
use App\Http\Controllers\Api\V1\LogementController;
use App\Http\Controllers\Api\V1\OccupantController;
use App\Http\Controllers\Api\V1\OccupationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('api.v1.login');

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.me');

        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('establishments', EstablishmentController::class);
        Route::apiResource('logements', LogementController::class);
        Route::apiResource('occupants', OccupantController::class);

        Route::apiResource('assignment-requests', AssignmentRequestController::class)
            ->only(['index', 'show', 'store']);
        Route::post('/assignment-requests/{assignmentRequest}/accept', [AssignmentRequestController::class, 'accept'])
            ->name('assignment-requests.accept');
        Route::post('/assignment-requests/{assignmentRequest}/reject', [AssignmentRequestController::class, 'reject'])
            ->name('assignment-requests.reject');
        Route::post('/assignment-requests/{assignmentRequest}/reset', [AssignmentRequestController::class, 'reset'])
            ->name('assignment-requests.reset');

        Route::apiResource('occupations', OccupationController::class)->only(['index', 'show']);
        Route::post('/occupations/{occupation}/end', [OccupationController::class, 'end'])
            ->name('occupations.end');
    });
});
