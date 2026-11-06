<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ktp_photo')->nullable()->after('nik');
            $table->date('birth_date')->nullable()->after('ktp_photo');
            $table->string('birth_place', 100)->nullable()->after('birth_date');
            $table->enum('gender', ['male', 'female'])->nullable()->after('birth_place');
            $table->string('religion', 50)->nullable()->after('gender');
            $table->string('occupation', 100)->nullable()->after('religion');
            $table->text('address_ktp')->nullable()->after('occupation');
            $table->string('rfid_tag', 60)->nullable()->unique()->after('address_ktp');
            $table->enum('verification_status', ['unverified', 'pending_rw', 'approved', 'rejected'])
                ->default('unverified')
                ->after('rfid_tag');
            $table->foreignId('verified_by_user_id')
                ->nullable()
                ->after('verification_status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by_user_id');
            $table->text('rejection_reason')->nullable()->after('verified_at');
            $table->boolean('gate_access_enabled')->default(false)->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by_user_id']);
            $table->dropColumn([
                'ktp_photo',
                'birth_date',
                'birth_place',
                'gender',
                'religion',
                'occupation',
                'address_ktp',
                'rfid_tag',
                'verification_status',
                'verified_by_user_id',
                'verified_at',
                'rejection_reason',
                'gate_access_enabled',
            ]);
        });
    }
};
