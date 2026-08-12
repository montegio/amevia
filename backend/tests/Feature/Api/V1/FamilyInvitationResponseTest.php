<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\Actions\InviteFamilyMemberAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\DTOs\InviteFamilyMemberData;
use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyInvitationResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_invited_user_can_accept_invitation(): void
    {
        [$membership, $invitedUser] = $this->createInvitation();

        $response = $this->patchJson(
            "/api/v1/family-members/{$membership->id}/accept",
            [
                'user_id' => $invitedUser->id,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('family_members', [
            'id' => $membership->id,
            'status' => 'accepted',
        ]);

        $this->assertNotNull(
            $membership->fresh()->joined_at
        );
    }

    public function test_invited_user_can_decline_invitation(): void
    {
        [$membership, $invitedUser] = $this->createInvitation();

        $response = $this->patchJson(
            "/api/v1/family-members/{$membership->id}/decline",
            [
                'user_id' => $invitedUser->id,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'declined');

        $this->assertDatabaseHas('family_members', [
            'id' => $membership->id,
            'status' => 'declined',
        ]);

        $this->assertNull(
            $membership->fresh()->joined_at
        );
    }

    public function test_another_user_cannot_accept_invitation(): void
    {
        [$membership] = $this->createInvitation();

        $anotherUser = User::factory()->create();

        $response = $this->patchJson(
            "/api/v1/family-members/{$membership->id}/accept",
            [
                'user_id' => $anotherUser->id,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('family_members', [
            'id' => $membership->id,
            'status' => 'pending',
        ]);
    }

    public function test_accepted_invitation_cannot_be_accepted_again(): void
    {
        [$membership, $invitedUser] = $this->createInvitation();

        $this->patchJson(
            "/api/v1/family-members/{$membership->id}/accept",
            [
                'user_id' => $invitedUser->id,
            ]
        )->assertOk();

        $this->patchJson(
            "/api/v1/family-members/{$membership->id}/accept",
            [
                'user_id' => $invitedUser->id,
            ]
        )->assertStatus(409)
    ->assertJson([
        'message' => 'Family invitation is no longer pending.',
        'code' => 'FAMILY_INVITATION_NOT_PENDING',
    ]);
    }

    private function createInvitation(): array
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        $membership = (new InviteFamilyMemberAction())->execute(
            new InviteFamilyMemberData(
                familyId: $family->id,
                userId: $invitedUser->id,
                invitedByUserId: $owner->id,
            )
        );

        return [$membership, $invitedUser];
    }
}
