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
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'starts_at')) {
                $table->dateTime('starts_at')->nullable()->after('scheduled_at');
            }
            if (! Schema::hasColumn('bookings', 'ends_at')) {
                $table->dateTime('ends_at')->nullable()->after('starts_at');
            }
            if (! Schema::hasColumn('bookings', 'resource_id')) {
                $table->unsignedBigInteger('resource_id')->nullable()->after('pet_id');
            }
            if (! Schema::hasColumn('bookings', 'assigned_user_id')) {
                $table->foreignId('assigned_user_id')->nullable()->after('resource_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('bookings', 'cancelled_reason')) {
                $table->text('cancelled_reason')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('bookings', 'source')) {
                $table->string('source', 20)->default('staff')->after('status');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION petora_reject_paid_order_money_update() RETURNS trigger AS $$
BEGIN
  IF OLD.status IN ('paid','completed','refunded','partially_refunded') AND OLD.paid_at IS NOT NULL THEN
    IF (NEW.subtotal IS DISTINCT FROM OLD.subtotal)
      OR (NEW.discount IS DISTINCT FROM OLD.discount)
      OR (NEW.tax IS DISTINCT FROM OLD.tax)
      OR (NEW.total IS DISTINCT FROM OLD.total)
      OR (NEW.branch_id IS DISTINCT FROM OLD.branch_id)
      OR (NEW.customer_id IS DISTINCT FROM OLD.customer_id) THEN
      RAISE EXCEPTION 'paid order financial fields are immutable';
    END IF;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS trg_orders_paid_immutable ON orders;
CREATE TRIGGER trg_orders_paid_immutable
BEFORE UPDATE ON orders
FOR EACH ROW EXECUTE FUNCTION petora_reject_paid_order_money_update();

CREATE OR REPLACE FUNCTION petora_reject_closed_cash_shift_update() RETURNS trigger AS $$
BEGIN
  IF OLD.status = 'closed' AND OLD.closed_at IS NOT NULL THEN
    RAISE EXCEPTION 'closed cash shift is immutable';
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS trg_cash_shifts_closed_immutable ON cash_shifts;
CREATE TRIGGER trg_cash_shifts_closed_immutable
BEFORE UPDATE ON cash_shifts
FOR EACH ROW EXECUTE FUNCTION petora_reject_closed_cash_shift_update();
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_orders_paid_immutable ON orders; DROP FUNCTION IF EXISTS petora_reject_paid_order_money_update();');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_cash_shifts_closed_immutable ON cash_shifts; DROP FUNCTION IF EXISTS petora_reject_closed_cash_shift_update();');
        }
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['source', 'cancelled_reason', 'assigned_user_id', 'resource_id', 'ends_at', 'starts_at'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    if ($col === 'assigned_user_id') {
                        try {
                            $table->dropConstrainedForeignId('assigned_user_id');
                        } catch (Throwable) {
                        }
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
