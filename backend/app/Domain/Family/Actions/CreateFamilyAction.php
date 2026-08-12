<?php

namespace App\Domain\Family\Actions;

use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\Models\Family;
use App\Domain\Family\Models\FamilyMember;
use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Domain\Family\Enums\FamilyRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateFamilyAction
{
    public function execute(CreateFamilyData $data): Family
    {
        return DB::transaction(function () use ($data) {
            $family = Family::create([
                'name' => $data->name,
                'slug' => $this->generateUniqueSlug(
                    $data->slug ?: $data->name
                ),
                'owner_user_id' => $data->ownerUserId,
                'timezone' => $data->timezone,
                'locale' => $data->locale,
                'status' => $data->status,
            ]);

            FamilyMember::create([
                'family_id' => $family->id,
                'user_id' => $data->ownerUserId,
		'role' => FamilyRole::OWNER,
		'status' => FamilyMemberStatus::ACCEPTED,
                'joined_at' => now(),
            ]);

            return $family;
        });
    }

    private function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Family::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
