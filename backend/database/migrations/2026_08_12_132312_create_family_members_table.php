<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('family_id')
                ->constrained('families')
                ->cascadeOnDelete();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('role', 30)
                ->default('contributor');

            $table->string('status', 30)
                ->default('pending');

            $table->foreignUuid('invited_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestampTz('invited_at')->nullable();
            $table->timestampTz('joined_at')->nullable();

            $table->timestampsTz();

            $table->unique(
                ['family_id', 'user_id'],
                'uq_family_members_family_user'
            );

            $table->index(['family_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
