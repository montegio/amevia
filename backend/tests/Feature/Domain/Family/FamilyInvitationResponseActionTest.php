<?php

namespace Tests\Feature\Domain\Family;

use App\Domain\Family\Actions\AcceptFamilyInvitationAction;
use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\Actions\DeclineFamilyInvitationAction;
use App\Domain\Family\Actions\InviteFamilyMemberAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\DTOs\InviteFamilyMemberData;
use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Models\User;
use App\Domain\Family\Exceptions\FamilyInvitationNotPendingException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyInvitationResponseActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_invitation_can_be_accepted(): void
    {
        $membership = $this->createPendingInvitation();

        $membership = (new AcceptFamilyInvitationAction())
            ->execute($membership);

        $this->assertSame(
            FamilyMemberStatus::ACCEPTED,
            $membership->status
        );

        $this->assertNotNull($membership->joined_at);
    }

    public function test_pending_invitation_can_be_declined(): void
    {
        $membership = $this->createPendingInvitation();

        $membership = (new DeclineFamilyInvitationAction())
            ->execute($membership);

        $this->assertSame(
            FamilyMemberStatus::DECLINED,
            $membership->status
        );

        $this->assertNull($membership->joined_at);
    }

    public function test_accepted_invitation_cannot_be_accepted_again(): void
    {
        $membership = $this->createPendingInvitation();

        $action = new AcceptFamilyInvitationAction();

        $action->execute($membership);

$this->expectException(
    FamilyInvitationNotPendingException::class
);
        $action->execute($membership->refresh());
    }

    public function test_declined_invitation_cannot_be_accepted(): void
    {
        $membership = $this->createPendingInvitation();

        (new DeclineFamilyInvitationAction())
            ->execute($membership);

$this->expectException(
    FamilyInvitationNotPendingException::class
);

        (new AcceptFamilyInvitationAction())
            ->execute($membership->refresh());
    }

    private function createPendingInvitation()
    {
        $owner = User::factory()->create();
        $invitedUser = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        return (new InviteFamilyMemberAction())->execute(
            new InviteFamilyMemberData(
                familyId: $family->id,
                userId: $invitedUser->id,
                invitedByUserId: $owner->id,
            )
        );
    }
}
