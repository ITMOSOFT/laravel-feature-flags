<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('feature-flags.tables.flags', 'feature_flags'), function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('enabled')->default(false);
            $table->unsignedTinyInteger('rollout_percentage')->default(100);
            $table->timestamps();
        });

        Schema::create(config('feature-flags.tables.assignments', 'feature_flag_assignments'), function (Blueprint $table): void {
            $table->id();
            $table->foreignId('feature_flag_id')
                ->constrained(config('feature-flags.tables.flags', 'feature_flags'))
                ->cascadeOnDelete();
            $table->string('subject_type');
            $table->string('subject_id');
            $table->boolean('enabled');
            $table->timestamps();
            $table->unique(
                ['feature_flag_id', 'subject_type', 'subject_id'],
                'feature_flag_subject_unique'
            );
            $table->index(['subject_type', 'subject_id'], 'feature_flag_subject_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('feature-flags.tables.assignments', 'feature_flag_assignments'));
        Schema::dropIfExists(config('feature-flags.tables.flags', 'feature_flags'));
    }
};
