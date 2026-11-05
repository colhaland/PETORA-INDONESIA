<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_branch_scopes')) {
            Schema::create('role_branch_scopes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->string('data_scope', 30)->default('own_branch');
                $table->timestamps();
                $table->unique(['role_id', 'branch_id']);
            });
        }

        if (! Schema::hasTable('user_branch_access')) {
            Schema::create('user_branch_access', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                $table->unique(['user_id', 'branch_id']);
            });
        }

        if (Schema::hasTable('user_branch_access') && Schema::hasTable('branch_user')) {
            $rows = DB::table('branch_user')->get();
            foreach ($rows as $row) {
                DB::table('user_branch_access')->insertOrIgnore([
                    'user_id' => $row->user_id,
                    'branch_id' => $row->branch_id,
                    'is_primary' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION petora_reject_final_medical_update() RETURNS trigger AS $$
BEGIN
  IF OLD.status = 'final' AND OLD.finalized_at IS NOT NULL THEN
    IF (NEW.subjective IS DISTINCT FROM OLD.subjective)
      OR (NEW.objective IS DISTINCT FROM OLD.objective)
      OR (NEW.assessment IS DISTINCT FROM OLD.assessment)
      OR (NEW.plan IS DISTINCT FROM OLD.plan)
      OR (NEW.pet_id IS DISTINCT FROM OLD.pet_id)
      OR (NEW.doctor_id IS DISTINCT FROM OLD.doctor_id)
      OR (NEW.branch_id IS DISTINCT FROM OLD.branch_id) THEN
      RAISE EXCEPTION 'final medical visit clinical fields are immutable; use revision';
    END IF;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS trg_medical_visits_final_immutable ON medical_visits;
CREATE TRIGGER trg_medical_visits_final_immutable
BEFORE UPDATE ON medical_visits
FOR EACH ROW EXECUTE FUNCTION petora_reject_final_medical_update();
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_medical_visits_final_immutable ON medical_visits; DROP FUNCTION IF EXISTS petora_reject_final_medical_update();');
        }
        Schema::dropIfExists('user_branch_access');
        Schema::dropIfExists('role_branch_scopes');
    }
};
