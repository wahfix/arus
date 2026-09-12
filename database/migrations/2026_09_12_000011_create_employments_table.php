<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->string('department')->nullable();
            $table->string('position');
            $table->string('employment_type')->nullable();
            $table->date('employment_start_date')->nullable();
            $table->integer('estimated_monthly_income')->nullable();
            $table->string('employment_status')->default('ACTIVE')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employments');
    }
};
