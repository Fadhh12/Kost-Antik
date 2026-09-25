<?php

use App\Enums\GenderTarget;
use App\Enums\SubmissionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('address', 255);
            $table->string('city', 100)->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('gender_target', GenderTarget::values());
            $table->text('description')->nullable();
            $table->string('contact_name', 100);
            $table->string('contact_phone', 30);
            $table->json('photos')->nullable();
            $table->enum('status', SubmissionStatus::values())->default(SubmissionStatus::Pending->value)->index();
            $table->string('rejection_reason', 255)->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('created_property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_submissions');
    }
};
