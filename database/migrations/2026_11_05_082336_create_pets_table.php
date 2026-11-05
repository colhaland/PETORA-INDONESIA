<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('pets', function (Blueprint $t) {
            $t->id();
            $t->string('pet_code')->unique();
            $t->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('species', 30);
            $t->string('breed')->nullable();
            $t->string('gender', 10)->nullable();
            $t->date('birth_date')->nullable();
            $t->decimal('weight', 8, 2)->nullable();
            $t->string('color')->nullable();
            $t->boolean('sterilized')->default(false);
            $t->json('allergies')->nullable();
            $t->string('status', 20)->default('active');
            $t->softDeletes();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pets'); }
};
