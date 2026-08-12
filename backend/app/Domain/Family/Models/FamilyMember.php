<?php

namespace App\Domain\Family\Models;

use App\Models\User;
use App\Domain\Family\Enums\FamilyMemberStatus;
use App\Domain\Family\Enums\FamilyRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyMember extends Model
{
    use HasUuids;

    protected $fillable = [
        'family_id',
        'user_id',
        'role',
        'status',
        'invited_by_user_id',
        'invited_at',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => FamilyRole::class,
	    'status' => FamilyMemberStatus::class,
	    'invited_at' => 'datetime',
            'joined_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
