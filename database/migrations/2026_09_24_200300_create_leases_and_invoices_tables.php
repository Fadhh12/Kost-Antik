<?php

use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('booking_request_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->index();
            $table->unsignedTinyInteger('duration_months');
            $table->unsignedBigInteger('monthly_price');
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->enum('status', LeaseStatus::values())->default(LeaseStatus::Active->value)->index();
            $table->date('terminated_at')->nullable();
            $table->string('termination_reason', 500)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['room_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->restrictOnDelete();
            $table->string('number', 30)->unique();
            $table->unsignedTinyInteger('sequence');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('due_date')->index();
            $table->unsignedBigInteger('amount');
            $table->enum('status', InvoiceStatus::values())->default(InvoiceStatus::Unpaid->value)->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['lease_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('leases');
    }
};
