<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\Models\FamilyMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyInviteMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_a_member(): void
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        $response = $this->postJson(
            "/api/v1/families/{$family->id}/members/invitations",
            [
                'user_id' => $invitedUser->id,
                'invited_by_user_id' => $owner->id,
                'role' => 'contributor',
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath('data.family_id', $family->id)
            ->assertJsonPath('data.user_id', $invitedUser->id)
            ->assertJsonPath('data.role', 'contributor')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('family_members', [
            'family_id' => $family->id,
            'user_id' => $invitedUser->id,
            'role' => 'contributor',
            'status' => 'pending',
        ]);
    }

    public function test_contributor_cannot_invite_a_member(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $contributor->id,
            'role' => 'contributor',
            'status' => 'accepted',
        ]);

        $response = $this->postJson(
            "/api/v1/families/{$family->id}/members/invitations",
            [
                'user_id' => $invitedUser->id,
                'invited_by_user_id' => $contributor->id,
                'role' => 'viewer',
            ]
        );

        $response->assertForbidden();
    }

    public function test_owner_role_is_rejected_by_validation(): void
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        $response = $this->postJson(
            "/api/v1/families/{$family->id}/members/invitations",
            [
                'user_id' => $invitedUser->id,
                'invited_by_user_id' => $owner->id,
                'role' => 'owner',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('role');
    }

    public function test_same_user_cannot_be_invited_twice(): void
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        $payload = [
            'user_id' => $invitedUser->id,
            'invited_by_user_id' => $owner->id,
            'role' => 'contributor',
        ];

        // Primeiro convite deve funcionar.
        $this->postJson(
            "/api/v1/families/{$family->id}/members/invitations",
            $payload
        )->assertCreated();

        // Segundo convite para o mesmo usuário deve gerar conflito.
        $this->postJson(
            "/api/v1/families/{$family->id}/members/invitations",
            $payload
        )
            ->assertStatus(409)
            ->assertJson([
                'message' => 'User already belongs to this family.',
                'code' => 'FAMILY_MEMBER_ALREADY_EXISTS',
            ]);
    }
}
