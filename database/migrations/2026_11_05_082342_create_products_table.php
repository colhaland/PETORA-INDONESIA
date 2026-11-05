<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('sku')->unique();
            $t->string('barcode')->nullable()->unique();
            $t->string('category')->nullable();
            $t->string('brand')->nullable();
            $t->text('description')->nullable();
            $t->string('unit')->default('pcs');
            $t->decimal('cost_price', 15, 2)->default(0);
            $t->decimal('sell_price', 15, 2)->default(0);
            $t->decimal('min_price', 15, 2)->default(0);
            $t->boolean('track_batch')->default(false);
            $t->boolean('track_expiry')->default(false);
            $t->string('status', 20)->default('active');
            $t->softDeletes();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
