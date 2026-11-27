<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('visitors', 'approval_status')) {
                $table->string('approval_status', 20)->default('approved')->after('guest_type');
            }
            if (!Schema::hasColumn('visitors', 'approval_notes')) {
                $table->string('approval_notes', 255)->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('visitors', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approval_notes');
            }
            if (!Schema::hasColumn('visitors', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('visitors', 'notes')) {
                $table->text('notes')->nullable()->after('vehicle_plate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn(['approval_status', 'approval_notes', 'approved_at', 'approved_by_user_id', 'notes']);
        });
    }
};
