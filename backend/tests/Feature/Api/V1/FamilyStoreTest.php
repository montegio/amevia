<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_family_through_the_api(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/families', [
            'name' => 'Família Monte',
            'owner_user_id' => $user->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Família Monte')
            ->assertJsonPath('data.slug', 'familia-monte')
            ->assertJsonPath('data.owner_user_id', $user->id)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('families', [
            'name' => 'Família Monte',
            'slug' => 'familia-monte',
            'owner_user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/families', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'owner_user_id',
            ]);
    }

    public function test_it_rejects_an_invalid_owner(): void
    {
        $response = $this->postJson('/api/v1/families', [
            'name' => 'Família Monte',
            'owner_user_id' => '019ff616-822d-718c-b905-2a7b0cb3ea59',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'owner_user_id',
            ]);
    }
}
