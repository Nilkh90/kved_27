<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tsConfig = 'simple';

        // SQLite/dev fallback: just add text column (used for LIKE search).
        if (DB::getDriverName() !== 'pgsql') {
            foreach (['kved_2010', 'nace_2027'] as $table) {
                if (! Schema::hasColumn($table, 'search_vector')) {
                    Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                        $blueprint->text('search_vector')->nullable();
                    });
                }
            }

            return;
        }

        // PostgreSQL: add tsvector column + GIN index + trigger to maintain it.
        foreach (['kved_2010', 'nace_2027'] as $table) {
            DB::statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS search_vector tsvector");
            DB::statement("CREATE INDEX IF NOT EXISTS {$table}_fts ON {$table} USING GIN(search_vector)");
        }

        DB::statement(<<<'SQL'
CREATE OR REPLACE FUNCTION nace_ua_update_search_vector() RETURNS trigger AS $$
BEGIN
  NEW.search_vector :=
    to_tsvector('simple',
      coalesce(NEW.code,'') || ' ' ||
      coalesce(NEW.title,'') || ' ' ||
      coalesce(NEW.description,'')
    );
  RETURN NEW;
END
$$ LANGUAGE plpgsql;
SQL);

        foreach (['kved_2010', 'nace_2027'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_search_vector_trigger ON {$table}");
            DB::statement(<<<SQL
CREATE TRIGGER {$table}_search_vector_trigger
BEFORE INSERT OR UPDATE ON {$table}
FOR EACH ROW EXECUTE FUNCTION nace_ua_update_search_vector();
SQL);
            DB::statement("UPDATE {$table} SET search_vector = to_tsvector('simple', coalesce(code,'') || ' ' || coalesce(title,'') || ' ' || coalesce(description,''))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            foreach (['kved_2010', 'nace_2027'] as $table) {
                if (Schema::hasColumn($table, 'search_vector')) {
                    Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                        $blueprint->dropColumn('search_vector');
                    });
                }
            }

            return;
        }

        foreach (['kved_2010', 'nace_2027'] as $table) {
            DB::statement("DROP TRIGGER IF EXISTS {$table}_search_vector_trigger ON {$table}");
            DB::statement("DROP INDEX IF EXISTS {$table}_fts");
            DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS search_vector");
        }

        DB::statement('DROP FUNCTION IF EXISTS nace_ua_update_search_vector()');
    }
};

