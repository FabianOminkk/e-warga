<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('host_house_id')->constrained('houses')->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('guest_name', 150);
            $table->enum('guest_type', ['guest', 'courier', 'ride_hailing', 'contractor'])->default('guest');
            $table->string('vehicle_plate', 30)->nullable();
            $table->string('qr_token', 64)->unique();
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->boolean('is_used')->default(false);
            $table->foreignId('checked_in_by_satpam_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};

