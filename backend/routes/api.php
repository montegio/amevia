<?php

use App\Http\Controllers\Api\V1\FamilyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/families', [FamilyController::class, 'store']);

    Route::post(
        '/families/{family}/members/invitations',
        [FamilyController::class, 'inviteMember']
    );

Route::patch(
    '/family-members/{membership}/accept',
    [FamilyController::class, 'acceptInvitation']
);

Route::patch(
    '/family-members/{membership}/decline',
    [FamilyController::class, 'declineInvitation']
);

});
