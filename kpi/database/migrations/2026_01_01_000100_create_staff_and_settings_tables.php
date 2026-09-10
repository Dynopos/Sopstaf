<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            // A staff record can exist before a login account does, and outlives it.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code');
            $table->string('name');
            $table->foreignId('supervisor_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('joined_on');
            $table->date('left_on')->nullable();
            $table->boolean('is_active')->default(true);
            // Not everyone on the payroll is measured by the sales KPI - a
            // supervisor may run the shop without carrying a sales target.
            $table->boolean('is_assessed')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'employee_code']);
            $table->index(['business_id', 'is_active']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->json('value');
            $table->timestamps();

            $table->unique(['business_id', 'key']);
        });

        Schema::create('kpi_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('scope');            // individual | team
            $table->string('category_key');
            $table->string('category_label');
            $table->decimal('category_weight', 5, 2);
            $table->string('label');
            $table->text('desc_0');
            $table->text('desc_1');
            $table->text('desc_2');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_id', 'scope', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_criteria');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('staff');
    }
};
