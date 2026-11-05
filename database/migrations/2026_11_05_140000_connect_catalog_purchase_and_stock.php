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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('track_inventory')->default(false);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamp('stock_issued_at')->nullable();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('inventory_managed')->default(false);
            $table->boolean('track_expiry')->default(false);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total', 15, 2);
            $table->boolean('inventory_managed')->default(false);
            $table->boolean('track_batch')->default(false);
            $table->boolean('track_expiry')->default(false);
            $table->timestamps();
            $table->unique(['purchase_order_id', 'product_id']);
        });

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->restrictOnDelete();
            $table->string('idempotency_key')->nullable()->unique();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamp('stock_applied_at')->nullable();
            $table->timestamps();
        });

        DB::statement("CREATE UNIQUE INDEX stock_balances_lot_unique ON stock_balances (warehouse_id, product_id, COALESCE(batch_number, ''), COALESCE(expiry_date, '0001-01-01'))");

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
ALTER TABLE orders
    ADD CONSTRAINT orders_discount_disabled_check CHECK (discount = 0);
ALTER TABLE order_items
    ADD CONSTRAINT order_items_discount_disabled_check CHECK (discount = 0);
ALTER TABLE purchase_order_items
    ADD CONSTRAINT purchase_order_items_quantity_positive_check CHECK (quantity > 0),
    ADD CONSTRAINT purchase_order_items_unit_cost_nonnegative_check CHECK (unit_cost >= 0),
    ADD CONSTRAINT purchase_order_items_total_nonnegative_check CHECK (total >= 0);
ALTER TABLE goods_receipt_items
    ADD CONSTRAINT goods_receipt_items_quantity_positive_check CHECK (quantity > 0),
    ADD CONSTRAINT goods_receipt_items_unit_cost_nonnegative_check CHECK (unit_cost >= 0);
SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_discount_disabled_check;
ALTER TABLE order_items DROP CONSTRAINT IF EXISTS order_items_discount_disabled_check;
ALTER TABLE purchase_order_items
    DROP CONSTRAINT IF EXISTS purchase_order_items_quantity_positive_check,
    DROP CONSTRAINT IF EXISTS purchase_order_items_unit_cost_nonnegative_check,
    DROP CONSTRAINT IF EXISTS purchase_order_items_total_nonnegative_check;
ALTER TABLE goods_receipt_items
    DROP CONSTRAINT IF EXISTS goods_receipt_items_quantity_positive_check,
    DROP CONSTRAINT IF EXISTS goods_receipt_items_unit_cost_nonnegative_check;
SQL);
        }

        DB::statement('DROP INDEX IF EXISTS stock_balances_lot_unique');
        Schema::dropIfExists('goods_receipt_items');
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
            $table->dropForeign(['purchase_order_id']);
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
        });
        Schema::dropIfExists('purchase_order_items');
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['inventory_managed', 'track_expiry']);
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['batch_number', 'expiry_date']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['warehouse_id', 'stock_issued_at']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('track_inventory');
        });
    }
};
