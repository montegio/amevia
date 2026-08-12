<?php

namespace App\Http\Resources\Api\V1\Family;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FamilyMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'family_id' => $this->family_id,
            'user_id' => $this->user_id,
            'role' => $this->role->value,
            'status' => $this->status->value,
            'invited_by_user_id' => $this->invited_by_user_id,
            'invited_at' => $this->invited_at?->toISOString(),
            'joined_at' => $this->joined_at?->toISOString(),
        ];
    }
}
