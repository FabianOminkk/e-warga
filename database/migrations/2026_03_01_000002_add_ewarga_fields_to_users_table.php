<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('house_id')->nullable()->after('tenant_id')->constrained('houses')->nullOnDelete();
            $table->string('phone_number', 25)->nullable()->unique()->after('name');
            $table->enum('role', [
                'super_admin',
                'ketua_rw',
                'ketua_rt',
                'sekretaris',
                'bendahara',
                'satpam',
                'warga'
            ])->default('warga')->after('password');
            $table->boolean('is_family_head')->default(false)->after('role');
            $table->string('nik', 30)->nullable()->after('is_family_head');
            $table->enum('status', ['active', 'inactive', 'pending_approval'])->default('active')->after('nik');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['house_id']);
            $table->dropColumn([
                'tenant_id',
                'house_id',
                'phone_number',
                'role',
                'is_family_head',
                'nik',
                'status'
            ]);
        });
    }
};

