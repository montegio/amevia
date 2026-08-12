<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name', 150);
            $table->string('slug', 160)->unique();

	    $table->foreignUuid('owner_user_id')
	          ->constrained('users')
	          ->restrictOnDelete();

            $table->string('timezone', 50)
                ->default('America/Sao_Paulo');

            $table->string('locale', 10)
                ->default('pt-BR');

            $table->string('status', 30)
                ->default('active');

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
