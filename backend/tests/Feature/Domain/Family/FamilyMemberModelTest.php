<?php

namespace Tests\Feature\Domain\Family;

use App\Domain\Family\Models\Family;
use App\Domain\Family\Models\FamilyMember;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyMemberModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_member_belongs_to_family_and_user(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Monte',
            'slug' => 'familia-monte',
            'owner_user_id' => $owner->id,
        ]);

        $membership = FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $memberUser->id,
            'role' => 'contributor',
            'status' => 'accepted',
            'joined_at' => now(),
        ]);

        $this->assertTrue($membership->family->is($family));
        $this->assertTrue($membership->user->is($memberUser));
    }

    public function test_family_has_members(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Monte',
            'slug' => 'familia-monte',
            'owner_user_id' => $owner->id,
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $memberUser->id,
            'role' => 'viewer',
            'status' => 'accepted',
        ]);

        $this->assertCount(1, $family->members);
    }

    public function test_user_has_family_memberships(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Monte',
            'slug' => 'familia-monte',
            'owner_user_id' => $owner->id,
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $memberUser->id,
            'role' => 'contributor',
            'status' => 'accepted',
        ]);

        $this->assertCount(1, $memberUser->familyMemberships);
    }

    public function test_same_user_cannot_be_added_twice_to_same_family(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Monte',
            'slug' => 'familia-monte',
            'owner_user_id' => $owner->id,
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $memberUser->id,
            'role' => 'contributor',
            'status' => 'accepted',
        ]);

        $this->expectException(QueryException::class);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $memberUser->id,
            'role' => 'viewer',
            'status' => 'accepted',
        ]);
    }
public function test_role_and_status_are_cast_to_enums(): void
{
    $owner = User::factory()->create();

    $family = Family::create([
        'name' => 'Família Monte',
        'slug' => 'familia-monte',
        'owner_user_id' => $owner->id,
    ]);

    $membership = FamilyMember::create([
        'family_id' => $family->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'accepted',
    ]);

    $this->assertSame(
        \App\Domain\Family\Enums\FamilyRole::OWNER,
        $membership->role
    );

    $this->assertSame(
        \App\Domain\Family\Enums\FamilyMemberStatus::ACCEPTED,
        $membership->status
    );
}
public function test_owner_can_manage_members(): void
{
    $owner = User::factory()->create();

    $family = Family::create([
        'name' => 'Família Monte',
        'slug' => 'familia-monte',
        'owner_user_id' => $owner->id,
    ]);

    $membership = FamilyMember::create([
        'family_id' => $family->id,
        'user_id' => $owner->id,
        'role' => 'owner',
        'status' => 'accepted',
    ]);

    $this->assertTrue($membership->isOwner());
    $this->assertTrue($membership->canManageMembers());
}

public function test_admin_can_manage_members(): void
{
    $owner = User::factory()->create();
    $admin = User::factory()->create();

    $family = Family::create([
        'name' => 'Família Monte',
        'slug' => 'familia-monte',
        'owner_user_id' => $owner->id,
    ]);

    $membership = FamilyMember::create([
        'family_id' => $family->id,
        'user_id' => $admin->id,
        'role' => 'admin',
        'status' => 'accepted',
    ]);

    $this->assertTrue($membership->isAdmin());
    $this->assertTrue($membership->canManageMembers());
}

public function test_contributor_cannot_manage_members(): void
{
    $owner = User::factory()->create();
    $user = User::factory()->create();

    $family = Family::create([
        'name' => 'Família Monte',
        'slug' => 'familia-monte',
        'owner_user_id' => $owner->id,
    ]);

    $membership = FamilyMember::create([
        'family_id' => $family->id,
        'user_id' => $user->id,
        'role' => 'contributor',
        'status' => 'accepted',
    ]);

    $this->assertFalse($membership->canManageMembers());
}
}
