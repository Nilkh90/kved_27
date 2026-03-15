<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nace_2027', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('title', 500);
            $table->string('level', 20);
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('nace_2027')
                ->nullOnDelete();
            $table->text('description')->nullable();
            $table->json('includes')->nullable();
            $table->json('excludes')->nullable();
            $table->timestamps();

            $table->index('parent_id', 'idx_nace_parent');
            $table->index('code', 'idx_nace_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nace_2027');
    }
};

