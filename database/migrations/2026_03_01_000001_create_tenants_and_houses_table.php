<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->string('rt_rw', 50)->default('RT 01 / RW 01');
            $table->string('province', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('subscription_plan', 50)->default('starter');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('block_name', 20); // e.g. "Blok A"
            $table->string('house_number', 20); // e.g. "12"
            $table->text('full_address')->nullable();
            $table->enum('occupancy_status', ['occupied', 'vacant', 'rented'])->default('occupied');
            $table->timestamps();

            $table->unique(['tenant_id', 'block_name', 'house_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
        Schema::dropIfExists('tenants');
    }
};

