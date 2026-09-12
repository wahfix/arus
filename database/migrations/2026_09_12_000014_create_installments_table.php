<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->date('due_date')->index();
            $table->integer('principal_due');
            $table->integer('interest_due');
            $table->integer('penalty_due')->default(0);
            $table->integer('total_due');
            $table->integer('principal_paid')->default(0);
            $table->integer('interest_paid')->default(0);
            $table->integer('penalty_paid')->default(0);
            $table->integer('total_paid')->default(0);
            $table->integer('remaining_amount');
            $table->string('status')->default('PENDING')->index();
            $table->date('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['loan_id', 'installment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
