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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'two_factor_last_counter')) {
                $table->unsignedBigInteger('two_factor_last_counter')->nullable();
            }
        });

        Schema::create('two_factor_recovery_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash', 64);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'code_hash']);
        });

        Schema::create('super_admin_bootstraps', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('email')->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'idempotency_key')) {
                $table->string('idempotency_key', 100)->nullable()->unique();
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            $this->restrictFinancialAndMedicalForeignKeys();
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION petora_reject_final_medical_update() RETURNS trigger AS $$
BEGIN
  IF current_setting('petora.medical_revision', true) = 'on' THEN
    RETURN NEW;
  END IF;
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
SQL);
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION petora_audit_logs_immutable() RETURNS trigger AS $$
BEGIN
  RAISE EXCEPTION 'audit log records are immutable';
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS trg_audit_logs_immutable ON audit_logs;
CREATE TRIGGER trg_audit_logs_immutable
BEFORE UPDATE OR DELETE ON audit_logs
FOR EACH ROW EXECUTE FUNCTION petora_audit_logs_immutable();
DROP TRIGGER IF EXISTS trg_audit_logs_no_truncate ON audit_logs;
CREATE TRIGGER trg_audit_logs_no_truncate
BEFORE TRUNCATE ON audit_logs
FOR EACH STATEMENT EXECUTE FUNCTION petora_audit_logs_immutable();
SQL);
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
CREATE TRIGGER IF NOT EXISTS trg_audit_logs_no_update
BEFORE UPDATE ON audit_logs
BEGIN
  SELECT RAISE(ABORT, 'audit log records are immutable');
END;
CREATE TRIGGER IF NOT EXISTS trg_audit_logs_no_delete
BEFORE DELETE ON audit_logs
BEGIN
  SELECT RAISE(ABORT, 'audit log records are immutable');
END;
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_logs_immutable ON audit_logs; DROP TRIGGER IF EXISTS trg_audit_logs_no_truncate ON audit_logs; DROP FUNCTION IF EXISTS petora_audit_logs_immutable();');
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
SQL);
            $this->restoreFinancialAndMedicalForeignKeys();
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_logs_no_update; DROP TRIGGER IF EXISTS trg_audit_logs_no_delete;');
        }

        Schema::dropIfExists('two_factor_recovery_codes');
        Schema::dropIfExists('super_admin_bootstraps');
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'idempotency_key')) {
                $table->dropUnique(['idempotency_key']);
                $table->dropColumn('idempotency_key');
            }
        });
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'two_factor_last_counter')) {
                $table->dropColumn('two_factor_last_counter');
            }
        });
    }

    private function restrictFinancialAndMedicalForeignKeys(): void
    {
        foreach ([
            'medical_visits' => ['branch_id', 'pet_id', 'doctor_id'],
            'medical_record_revisions' => ['medical_visit_id'],
            'payments' => ['order_id'],
            'orders' => ['branch_id'],
            'bookings' => ['branch_id', 'customer_id'],
            'grooming_sessions' => ['branch_id', 'pet_id'],
            'expenses' => ['branch_id'],
            'cash_shifts' => ['branch_id', 'user_id'],
            'purchase_orders' => ['branch_id', 'supplier_id'],
            'goods_receipts' => ['purchase_order_id'],
        ] as $tableName => $columns) {
            foreach ($columns as $column) {
                $constraint = "{$tableName}_{$column}_foreign";
                DB::statement("ALTER TABLE {$tableName} DROP CONSTRAINT IF EXISTS {$constraint}");
                $referencedTable = match ($column) {
                    'branch_id' => 'branches',
                    'pet_id' => 'pets',
                    'doctor_id', 'user_id' => 'users',
                    'medical_visit_id' => 'medical_visits',
                    'order_id' => 'orders',
                    'customer_id' => 'customers',
                    'purchase_order_id' => 'purchase_orders',
                    'supplier_id' => 'suppliers',
                    default => throw new LogicException("Unsupported constrained column: {$column}"),
                };
                DB::statement("ALTER TABLE {$tableName} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$column}) REFERENCES {$referencedTable} (id) ON DELETE RESTRICT");
            }
        }
    }

    private function restoreFinancialAndMedicalForeignKeys(): void
    {
        foreach ([
            'medical_visits' => ['branch_id' => ['branches', 'CASCADE'], 'pet_id' => ['pets', 'CASCADE'], 'doctor_id' => ['users', 'CASCADE']],
            'medical_record_revisions' => ['medical_visit_id' => ['medical_visits', 'CASCADE']],
            'payments' => ['order_id' => ['orders', 'CASCADE']],
            'orders' => ['branch_id' => ['branches', 'CASCADE']],
            'bookings' => ['branch_id' => ['branches', 'CASCADE'], 'customer_id' => ['customers', 'CASCADE']],
            'grooming_sessions' => ['branch_id' => ['branches', 'CASCADE'], 'pet_id' => ['pets', 'CASCADE']],
            'expenses' => ['branch_id' => ['branches', 'CASCADE']],
            'cash_shifts' => ['branch_id' => ['branches', 'CASCADE'], 'user_id' => ['users', 'CASCADE']],
            'purchase_orders' => ['branch_id' => ['branches', 'CASCADE'], 'supplier_id' => ['suppliers', 'CASCADE']],
            'goods_receipts' => ['purchase_order_id' => ['purchase_orders', 'CASCADE']],
        ] as $tableName => $foreignKeys) {
            foreach ($foreignKeys as $column => [$referencedTable, $onDelete]) {
                $constraint = "{$tableName}_{$column}_foreign";
                DB::statement("ALTER TABLE {$tableName} DROP CONSTRAINT IF EXISTS {$constraint}");
                DB::statement("ALTER TABLE {$tableName} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$column}) REFERENCES {$referencedTable} (id) ON DELETE {$onDelete}");
            }
        }
    }
};
