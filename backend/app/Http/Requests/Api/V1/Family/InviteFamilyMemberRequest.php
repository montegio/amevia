<?php

namespace App\Http\Requests\Api\V1\Family;

use App\Domain\Family\Enums\FamilyRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteFamilyMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'uuid',
                'exists:users,id',
            ],

            'invited_by_user_id' => [
                'required',
                'uuid',
                'exists:users,id',
            ],

            'role' => [
                'required',
                Rule::enum(FamilyRole::class),
                Rule::notIn([FamilyRole::OWNER->value]),
            ],
        ];
    }
}
