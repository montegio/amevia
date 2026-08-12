<?php

namespace Tests\Feature\Domain\Family;

use App\Domain\Family\Actions\CreateFamilyAction;
use App\Domain\Family\DTOs\CreateFamilyData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateFamilyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_family(): void
    {
        $user = User::factory()->create();

        $data = new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
        );

        $family = (new CreateFamilyAction())->execute($data);

        $this->assertDatabaseHas('families', [
            'id' => $family->id,
            'name' => 'Família Monte',
            'slug' => 'familia-monte',
            'owner_user_id' => $user->id,
            'timezone' => 'America/Sao_Paulo',
            'locale' => 'pt-BR',
            'status' => 'active',
        ]);

        $this->assertTrue($family->owner->is($user));
    }

    public function test_it_accepts_a_custom_slug(): void
    {
        $user = User::factory()->create();

        $data = new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
            slug: 'minha-familia',
        );

        $family = (new CreateFamilyAction())->execute($data);

        $this->assertSame('minha-familia', $family->slug);
    }

public function test_it_generates_a_unique_slug_when_slug_already_exists(): void
{
    $user = User::factory()->create();

    $action = new CreateFamilyAction();

    $firstFamily = $action->execute(
        new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
        )
    );

    $secondFamily = $action->execute(
        new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
        )
    );

    $thirdFamily = $action->execute(
        new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
        )
    );

    $this->assertSame('familia-monte', $firstFamily->slug);
    $this->assertSame('familia-monte-2', $secondFamily->slug);
    $this->assertSame('familia-monte-3', $thirdFamily->slug);
}

public function test_it_does_not_reuse_slug_from_soft_deleted_family(): void
{
    $user = User::factory()->create();

    $action = new CreateFamilyAction();

    $firstFamily = $action->execute(
        new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
        )
    );

    $firstFamily->delete();

    $secondFamily = $action->execute(
        new CreateFamilyData(
            name: 'Família Monte',
            ownerUserId: $user->id,
        )
    );

    $this->assertSame('familia-monte-2', $secondFamily->slug);
}
}
