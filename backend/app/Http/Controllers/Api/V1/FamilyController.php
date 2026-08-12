<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Family\StoreFamilyRequest;
use App\Http\Resources\Api\V1\Family\FamilyResource;
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
}
