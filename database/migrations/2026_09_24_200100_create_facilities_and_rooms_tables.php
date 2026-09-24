<?php

use App\Enums\FacilityType;
use App\Enums\RoomStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('icon', 50)->nullable();
            $table->enum('type', FacilityType::values())->index();
            $table->timestamps();
        });

        Schema::create('facility_property', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->primary(['property_id', 'facility_id']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->string('code', 20);
            $table->unsignedTinyInteger('floor')->nullable();
            $table->decimal('size_m2', 5, 2)->nullable();
            $table->unsignedBigInteger('monthly_price');
            $table->unsignedTinyInteger('capacity')->default(1);
            $table->enum('status', RoomStatus::values())->default(RoomStatus::Available->value)->index();
            $table->timestamps();

            $table->unique(['property_id', 'code']);
        });

        Schema::create('facility_room', function (Blueprint $table) {
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->primary(['room_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_room');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('facility_property');
        Schema::dropIfExists('facilities');
    }
};
