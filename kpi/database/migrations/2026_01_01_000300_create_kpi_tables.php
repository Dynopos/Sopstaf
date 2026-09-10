<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('status')->default('open'); // open | closing | locked | reopened
            // The configuration in force when the period opened. Every figure for
            // this month is computed from here, never from current settings - a
            // target raised in March must not rewrite January.
            $table->json('config_snapshot');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'year', 'month']);
        });

        Schema::create('kpi_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->bigInteger('sales_amount_cents')->default(0);
            $table->decimal('sales_score', 5, 2)->default(0);
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('returned_reason')->nullable();
            $table->unsignedInteger('version')->default(1); // optimistic locking
            $table->timestamps();

            $table->unique(['period_id', 'staff_id']);
            $table->index(['period_id', 'status']);
        });

        Schema::create('kpi_assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('kpi_assessments')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('kpi_criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0); // 0 | 1 | 2
            $table->text('note')->nullable();                 // required when score = 0
            $table->timestamps();

            $table->unique(['assessment_id', 'criteria_id']);
        });

        Schema::create('team_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->bigInteger('sales_amount_cents')->default(0);
            $table->decimal('sales_score', 5, 2)->default(0);
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->decimal('total_score', 5, 2)->default(0);
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('returned_reason')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            $table->unique('period_id');
        });

        Schema::create('team_assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_assessment_id')->constrained('team_assessments')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('kpi_criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['team_assessment_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_assessment_items');
        Schema::dropIfExists('team_assessments');
        Schema::dropIfExists('kpi_assessment_items');
        Schema::dropIfExists('kpi_assessments');
        Schema::dropIfExists('kpi_periods');
    }
};
