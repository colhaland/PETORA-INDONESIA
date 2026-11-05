<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('customer_number')->unique();
            $t->string('name');
            $t->string('phone')->nullable()->index();
            $t->string('email')->nullable();
            $t->date('birth_date')->nullable();
            $t->string('gender', 10)->nullable();
            $t->text('internal_notes')->nullable();
            $t->string('status', 20)->default('active');
            $t->softDeletes();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
