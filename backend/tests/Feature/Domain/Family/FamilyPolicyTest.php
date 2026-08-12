<?php

namespace Tests\Feature\Domain\Family;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\Models\FamilyMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_manage_family_members(): void
    {
        $owner = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        $this->assertTrue(
            $owner->can('manageMembers', $family)
        );
    }

    public function test_admin_can_manage_family_members(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $admin->id,
            'role' => 'admin',
            'status' => 'accepted',
        ]);

        $this->assertTrue(
            $admin->can('manageMembers', $family)
        );
    }

    public function test_contributor_cannot_manage_family_members(): void
    {
        $owner = User::factory()->create();
        $contributor = User::factory()->create();

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

        $this->assertFalse(
            $contributor->can('manageMembers', $family)
        );
    }

    public function test_accepted_member_can_view_family(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $family = (new CreateFamilyAction())->execute(
            new CreateFamilyData(
                name: 'Família Monte',
                ownerUserId: $owner->id,
            )
        );

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $viewer->id,
            'role' => 'viewer',
            'status' => 'accepted',
        ]);

        $this->assertTrue(
            $viewer->can('view', $family)
        );
    }
}
