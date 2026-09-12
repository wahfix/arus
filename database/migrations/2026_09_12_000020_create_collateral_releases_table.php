<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collateral_releases', function (Blueprint $table) {
            $table->id();
            $table->string('release_number')->unique();
            $table->foreignId('collateral_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('verified_identity_id')->nullable()->constrained('identity_verifications')->nullOnDelete();
            $table->string('released_to_name');
            $table->string('relationship_to_customer')->nullable();
            $table->date('release_date')->index();
            $table->string('release_location')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('witness_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_signature_reference')->nullable();
            $table->text('handover_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collateral_releases');
    }
};
