<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('house_id')->constrained('houses')->cascadeOnDelete();
            $table->date('billing_period'); // YYYY-MM-01
            $table->decimal('amount', 12, 2)->default(150000.00);
            $table->string('description')->default('Iuran Kebersihan, Keamanan, & Kas RT');
            $table->enum('status', ['unpaid', 'pending_verification', 'paid', 'overdue'])->default('unpaid');
            $table->date('due_date');
            $table->timestamps();

            $table->unique(['tenant_id', 'house_id', 'billing_period']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_id')->constrained('billings')->cascadeOnDelete();
            $table->foreignId('payer_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('payment_method', ['manual_transfer', 'qris', 'cash'])->default('manual_transfer');
            $table->string('proof_image')->nullable();
            $table->decimal('amount_paid', 12, 2);
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->string('category', 100); // e.g. "Iuran Warga", "Gaji Satpam", "Perbaikan Got"
            $table->string('title', 200);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_books');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('billings');
    }
};

