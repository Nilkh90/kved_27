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
        Schema::create('transition_mapping', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('old_kved_id')
                ->constrained('kved_2010')
                ->cascadeOnDelete();
            $table->foreignId('new_nace_id')
                ->constrained('nace_2027')
                ->cascadeOnDelete();
            $table->string('transition_type', 10);
            $table->boolean('action_required')->default(false);
            $table->text('transition_comment')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('transition_mapping', function (Blueprint $table): void {
            $table->index('old_kved_id', 'idx_mapping_kved');
            $table->index('new_nace_id', 'idx_mapping_nace');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transition_mapping');
    }
};

