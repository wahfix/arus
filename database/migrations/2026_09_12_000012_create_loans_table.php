<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->integer('principal_amount');
            $table->integer('interest_rate_basis_points');
            $table->string('interest_method');
            $table->integer('tenor');
            $table->string('installment_frequency');
            $table->date('disbursement_date')->nullable();
            $table->date('first_due_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->integer('total_interest')->default(0);
            $table->integer('total_payable')->default(0);
            $table->integer('installment_amount')->default(0);
            $table->integer('outstanding_principal')->default(0);
            $table->integer('outstanding_interest')->default(0);
            $table->integer('outstanding_penalty')->default(0);
            $table->integer('outstanding_total')->default(0);
            $table->string('status')->default('DRAFT')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
