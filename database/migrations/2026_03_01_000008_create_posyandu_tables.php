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
        Schema::create('posyandu_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('family_card_number', 20)->nullable(); // No KK
            $table->string('nik', 20)->nullable();
            $table->string('name');
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->date('birth_date');
            $table->enum('category', ['balita', 'anak', 'ibu_hamil', 'ibu', 'lansia'])->default('balita');
            $table->string('parent_name')->nullable(); // Nama orang tua / penanggung jawab
            $table->string('address')->nullable();     // Blok / No Rumah
            $table->string('phone', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('posyandu_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('posyandu_member_id')->constrained('posyandu_members')->cascadeOnDelete();
            $table->date('checkup_date');
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 2)->nullable();
            $table->decimal('head_circumference_cm', 5, 2)->nullable();
            $table->decimal('arm_circumference_cm', 5, 2)->nullable(); // LiLA
            $table->string('blood_pressure', 20)->nullable(); // e.g. 120/80
            $table->integer('blood_sugar')->nullable();        // mg/dL
            $table->integer('cholesterol')->nullable();        // mg/dL
            $table->decimal('uric_acid', 4, 1)->nullable();    // mg/dL
            $table->string('gestational_age_weeks')->nullable(); // Usia kehamilan (khusus bumil)
            $table->string('nutrition_status')->default('normal'); // 'gizi_baik', 'risiko_stunting', 'gizi_kurang', 'gizi_lebih', 'normal', 'hipertensi', 'risiko_tinggi'
            $table->string('vitamins_or_vaccines')->nullable();
            $table->string('examiner_name')->default('Bidan Desa / Kader Posyandu');
            $table->text('action_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posyandu_records');
        Schema::dropIfExists('posyandu_members');
    }
};

