<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Kolom environment_type ke tabel tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('environment_type', ['cluster', 'kampung'])
                  ->default('cluster')
                  ->after('subscription_plan');
        });

        // 2. Buat Tabel Jadwal Keamanan (Satpam Shift & Ronda Siskamling Warga)
        Schema::create('security_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('schedule_type', ['satpam_shift', 'ronda_warga'])->default('satpam_shift');
            $table->date('duty_date');
            $table->string('shift_name', 100); // e.g. "Shift Pagi (07:00-15:00)" atau "Regu Ronda Malam B"
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['scheduled', 'present', 'swapped', 'absent'])->default('scheduled');
            $table->foreignId('replacement_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'duty_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_schedules');
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('environment_type');
        });
    }
};
