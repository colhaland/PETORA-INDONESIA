<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('medical_visits', function (Blueprint $t) {
            $t->id();
            $t->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $t->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $t->string('status', 20)->default('draft');
            $t->text('subjective')->nullable();
            $t->text('objective')->nullable();
            $t->text('assessment')->nullable();
            $t->text('plan')->nullable();
            $t->boolean('visible_to_owner')->default(true);
            $t->timestamp('finalized_at')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('medical_visits'); }
};
