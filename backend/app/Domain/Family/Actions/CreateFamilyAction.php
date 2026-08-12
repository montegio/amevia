<?php

namespace App\Domain\Family\Actions;

use App\Domain\Family\DTOs\CreateFamilyData;
use App\Domain\Family\Models\Family;
use Illuminate\Support\Str;

final class CreateFamilyAction
{
    public function execute(CreateFamilyData $data): Family
    {
        return Family::create([
            'name' => $data->name,
            'slug' => $this->generateUniqueSlug(
                $data->slug ?: $data->name
            ),
            'owner_user_id' => $data->ownerUserId,
            'timezone' => $data->timezone,
            'locale' => $data->locale,
            'status' => $data->status,
        ]);
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
