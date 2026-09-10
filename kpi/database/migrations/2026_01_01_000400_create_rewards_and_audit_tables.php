<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->decimal('kpi_total', 5, 2)->default(0);
            $table->bigInteger('individual_bonus_cents')->default(0);
            $table->bigInteger('high_sales_incentive_cents')->default(0);
            $table->bigInteger('team_bonus_cents')->default(0);
            $table->bigInteger('total_cents')->default(0);
            // Why each figure is what it is: the inputs and the band matched.
            // When somebody asks why their bonus dropped RM100, the answer has
            // to be something you can point at.
            $table->json('calc_snapshot')->nullable();
            $table->string('status')->default('calculated'); // calculated | verified | paid | withheld
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('withheld_reason')->nullable();
            $table->timestamps();

            $table->unique(['period_id', 'staff_id']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->json('changes')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['business_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('rewards');
    }
};
