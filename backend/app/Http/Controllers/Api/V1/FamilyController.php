<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Family\StoreFamilyRequest;
use App\Http\Resources\Api\V1\Family\FamilyResource;
use App\Domain\Family\Actions\InviteFamilyMemberAction;
use App\Domain\Family\DTOs\InviteFamilyMemberData;
use App\Domain\Family\Enums\FamilyRole;
use App\Domain\Family\Models\Family;
use App\Http\Requests\Api\V1\Family\InviteFamilyMemberRequest;
use App\Http\Resources\Api\V1\Family\FamilyMemberResource;
use App\Domain\Family\Actions\AcceptFamilyInvitationAction;
use App\Domain\Family\Actions\DeclineFamilyInvitationAction;
use App\Domain\Family\Models\FamilyMember;
use App\Http\Requests\Api\V1\Family\RespondFamilyInvitationRequest;
use Illuminate\Http\JsonResponse;

class FamilyController extends Controller
{
    public function store(
        StoreFamilyRequest $request,
        CreateFamilyAction $action,
    ): JsonResponse {
        $validated = $request->validated();

        $family = $action->execute(
            new CreateFamilyData(
                name: $validated['name'],
                ownerUserId: $validated['owner_user_id'],
                slug: $validated['slug'] ?? null,
                timezone: $validated['timezone'] ?? 'America/Sao_Paulo',
                locale: $validated['locale'] ?? 'pt-BR',
            )
        );

        return (new FamilyResource($family))
            ->response()
            ->setStatusCode(201);
    }

public function inviteMember(
    InviteFamilyMemberRequest $request,
    Family $family,
    InviteFamilyMemberAction $action,
): JsonResponse {
    $validated = $request->validated();

    $membership = $action->execute(
        new InviteFamilyMemberData(
            familyId: $family->id,
            userId: $validated['user_id'],
            invitedByUserId: $validated['invited_by_user_id'],
            role: FamilyRole::from($validated['role']),
        )
    );

    return (new FamilyMemberResource($membership))
        ->response()
        ->setStatusCode(201);
}

public function acceptInvitation(
    RespondFamilyInvitationRequest $request,
    FamilyMember $membership,
    AcceptFamilyInvitationAction $action,
): JsonResponse {
    $validated = $request->validated();

    if ($membership->user_id !== $validated['user_id']) {
        abort(403);
    }

    $membership = $action->execute($membership);

    return (new FamilyMemberResource($membership))
        ->response()
        ->setStatusCode(200);
}

public function declineInvitation(
    RespondFamilyInvitationRequest $request,
    FamilyMember $membership,
    DeclineFamilyInvitationAction $action,
): JsonResponse {
    $validated = $request->validated();

    if ($membership->user_id !== $validated['user_id']) {
        abort(403);
    }

    $membership = $action->execute($membership);

    return (new FamilyMemberResource($membership))
        ->response()
        ->setStatusCode(200);
}

}
