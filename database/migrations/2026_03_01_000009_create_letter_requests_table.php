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
        Schema::create('letter_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('letter_type'); // 'pengantar_ktp', 'pengantar_kk', 'domisili', 'sktm', 'izin_usaha', 'pengantar_nikah', 'kematian'
            $table->string('letter_number')->nullable()->unique(); // Nomor surat resmi (e.g. 470/012/RT.04/RW.02/XI/2026)
            $table->string('applicant_name');
            $table->string('nik', 20)->nullable();
            $table->string('house_block', 50)->nullable();
            $table->text('purpose'); // Keperluan pembuatan surat
            $table->text('supporting_data')->nullable(); // JSON atau keterangan tambahan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('qr_verification_code', 64)->nullable()->unique();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_requests');
    }
};
