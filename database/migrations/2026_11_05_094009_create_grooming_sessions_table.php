<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grooming_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $t->foreignId('grooming_service_id')->nullable()->constrained('grooming_services')->nullOnDelete();
            $t->foreignId('groomer_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('status', 20)->default('pending');
            $t->timestamp('started_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grooming_sessions');
    }
};
