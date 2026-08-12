<?php

namespace Tests\Feature\Domain\Family;

use App\Domain\Family\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FamilyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_generates_uuid_automatically(): void
    {
        $user = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Teste',
            'slug' => 'familia-teste',
            'owner_user_id' => $user->id,
        ]);

        $this->assertNotNull($family->id);
        $this->assertTrue(Str::isUuid($family->id));
    }

    public function test_family_belongs_to_an_owner(): void
    {
        $user = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Teste',
            'slug' => 'familia-teste',
            'owner_user_id' => $user->id,
        ]);

        $this->assertTrue($family->owner->is($user));
    }

    public function test_user_can_own_families(): void
    {
        $user = User::factory()->create();

        Family::create([
            'name' => 'Família Um',
            'slug' => 'familia-um',
            'owner_user_id' => $user->id,
        ]);

        Family::create([
            'name' => 'Família Dois',
            'slug' => 'familia-dois',
            'owner_user_id' => $user->id,
        ]);

        $this->assertCount(2, $user->ownedFamilies);
    }

    public function test_family_uses_soft_delete(): void
    {
        $user = User::factory()->create();

        $family = Family::create([
            'name' => 'Família Teste',
            'slug' => 'familia-teste',
            'owner_user_id' => $user->id,
        ]);

        $family->delete();

        $this->assertSoftDeleted('families', [
            'id' => $family->id,
        ]);

        $this->assertNull(Family::find($family->id));
        $this->assertNotNull(
            Family::withTrashed()->find($family->id)
        );
    }
}
