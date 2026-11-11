<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 100); // e.g. "Gerbang Utama Pos Timur"
            $table->string('device_uid', 100)->unique();
            $table->string('secret_token', 255)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('online');
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamps();
        });

        Schema::create('gate_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('smart_gate_id')->constrained('smart_gates')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->enum('access_method', ['mobile_button', 'rfid_card', 'qr_code', 'satpam_manual', 'simulator'])->default('mobile_button');
            $table->enum('direction', ['entry', 'exit'])->default('entry');
            $table->enum('status', ['granted', 'denied'])->default('granted');
            $table->string('reason', 255)->nullable();
            $table->string('snapshot_image_url', 255)->nullable();
            $table->timestamp('triggered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gate_logs');
        Schema::dropIfExists('smart_gates');
    }
};

