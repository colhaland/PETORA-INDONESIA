<?php
declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('grooming_services', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('status', 20)->default('active');
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('grooming_services'); }
};
