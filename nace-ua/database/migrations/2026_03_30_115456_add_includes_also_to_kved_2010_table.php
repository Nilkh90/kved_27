<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kved_2010', function (Blueprint $table) {
            $table->json('includes_also')->nullable()->after('includes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kved_2010', function (Blueprint $table) {
            $table->dropColumn('includes_also');
        });
    }
};
