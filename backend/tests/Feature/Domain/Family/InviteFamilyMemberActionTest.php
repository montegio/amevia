<?php

namespace Tests\Feature\Domain\Family;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\Actions\InviteFamilyMemberAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\DTOs\InviteFamilyMemberData;
use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Domain\Family\Enums\FamilyRole;
use App\Domain\Family\Exceptions\FamilyMemberAlreadyExistsException;
use App\Domain\Family\Exceptions\OwnerRoleCannotBeInvitedException;
use App\Domain\Family\Models\FamilyMember;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteFamilyMemberActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_invites_a_user_to_family(): void
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
                role: FamilyRole::CONTRIBUTOR,
            )
        );

        $this->assertSame(
            FamilyRole::CONTRIBUTOR,
            $membership->role
        );

        $this->assertSame(
            FamilyMemberStatus::PENDING,
            $membership->status
        );

        $this->assertSame(
            $owner->id,
            $membership->invited_by_user_id
        );

        $this->assertNotNull($membership->invited_at);
        $this->assertNull($membership->joined_at);
    }

    public function test_owner_role_cannot_be_assigned_through_invitation(): void
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        $this->expectException(
            OwnerRoleCannotBeInvitedException::class
        );

        (new InviteFamilyMemberAction())->execute(
            new InviteFamilyMemberData(
                familyId: $family->id,
                userId: $invitedUser->id,
                invitedByUserId: $owner->id,
                role: FamilyRole::OWNER,
            )
        );
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

        $action = new InviteFamilyMemberAction();

        $data = new InviteFamilyMemberData(
            familyId: $family->id,
            userId: $invitedUser->id,
            invitedByUserId: $owner->id,
        );

        $action->execute($data);

        $this->expectException(
            FamilyMemberAlreadyExistsException::class
        );

        $action->execute($data);
    }

    public function test_contributor_cannot_invite_members(): void
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

        $this->expectException(
            AuthorizationException::class
        );

        (new InviteFamilyMemberAction())->execute(
            new InviteFamilyMemberData(
                familyId: $family->id,
                userId: $invitedUser->id,
                invitedByUserId: $contributor->id,
            )
        );
    }
}
