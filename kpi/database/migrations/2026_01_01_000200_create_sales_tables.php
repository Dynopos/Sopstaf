<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->date('sold_on');
            // Money is stored in whole cents. Never a float - bonuses get audited.
            $table->bigInteger('amount_cents')->default(0);
            $table->integer('focus_qty')->default(0);
            $table->text('note')->nullable();
            $table->string('source')->default('manual'); // manual | import | pos
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One row per staff per day. Without this a double entry doubles
            // somebody's month, and their bonus with it.
            $table->unique(['staff_id', 'sold_on']);
            $table->index(['business_id', 'sold_on']);
        });

        Schema::create('sales_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->date('adjusted_on');
            $table->date('original_sale_date')->nullable();
            $table->bigInteger('amount_cents')->default(0); // may be negative
            $table->integer('focus_qty')->default(0);       // may be negative
            $table->text('reason');
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'adjusted_on']);
            $table->index(['staff_id', 'adjusted_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_adjustments');
        Schema::dropIfExists('daily_sales');
    }
};
