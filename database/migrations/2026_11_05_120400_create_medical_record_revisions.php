<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('medical_record_revisions')) {
            Schema::create('medical_record_revisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medical_visit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('revised_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('reason')->nullable();
                $table->json('previous_snapshot')->nullable();
                $table->timestamp('revised_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_revisions');
    }
};
