<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctor_ward', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');  // Foreign key to users table (doctor)
            $table->uuid('ward_id');  // Foreign key to wards table
            $table->date('assigned_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('cascade');

            // Unique constraint to prevent duplicate assignments
            $table->unique(['user_id', 'ward_id'], 'doctor_ward_user_ward_unique');

            // Indexes for performance
            $table->index(['user_id', 'is_active']);
            $table->index(['ward_id', 'is_active']);
            $table->index('assigned_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_ward');
    }
};
