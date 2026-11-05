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
        Schema::table('stock_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_movements', 'movement_no')) {
                $table->string('movement_no')->nullable()->after('id');
            }
            if (! Schema::hasColumn('stock_movements', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('stock_movements', 'unit_cost')) {
                $table->decimal('unit_cost', 15, 2)->default(0)->after('quantity');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION petora_reject_stock_movement_mutation() RETURNS trigger AS $$
BEGIN
  RAISE EXCEPTION 'stock_movements is append-only';
END;
$$ LANGUAGE plpgsql;
DROP TRIGGER IF EXISTS trg_stock_movements_no_update ON stock_movements;
CREATE TRIGGER trg_stock_movements_no_update
BEFORE UPDATE OR DELETE ON stock_movements
FOR EACH ROW EXECUTE FUNCTION petora_reject_stock_movement_mutation();
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_stock_movements_no_update ON stock_movements; DROP FUNCTION IF EXISTS petora_reject_stock_movement_mutation();');
        }
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            foreach (['unit_cost', 'batch_id', 'movement_no'] as $col) {
                if (Schema::hasColumn('stock_movements', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
