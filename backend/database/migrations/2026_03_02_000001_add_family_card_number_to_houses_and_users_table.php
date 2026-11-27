<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->string('no_kk', 20)->nullable()->index()->after('house_number');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('no_kk', 20)->nullable()->index()->after('nik');
        });
    }

    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropIndex(['no_kk']);
            $table->dropColumn('no_kk');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['no_kk']);
            $table->dropColumn('no_kk');
        });
    }
};
