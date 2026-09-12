<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaterals', function (Blueprint $table) {
            $table->id();
            $table->string('collateral_code')->unique();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('collateral_type');
            $table->text('description')->nullable();
            $table->string('identification_number')->nullable()->index();
            $table->integer('estimated_value')->nullable();
            $table->date('received_date')->nullable();
            $table->text('condition_on_receipt')->nullable();
            $table->string('storage_location')->nullable();
            $table->string('custody_status')->default('PENDING')->index();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaterals');
    }
};
