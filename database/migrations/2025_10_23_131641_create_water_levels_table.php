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
        Schema::create('water_levels', function (Blueprint $table) {
            $table->id();

            $table->string('device_id')->index()->comment('Identifier for the transmitting NodeMCU/ESP8266.');
            $table->decimal('level_cm', 8, 2)->comment('The measured water level in centimeters.');
            $table->json('raw_data')->nullable()->comment('Optional raw data payload from the sensor.');
            $table->timestamp('timestamp')->nullable()->comment('Device timestamp if provided.');
            $table->decimal('battery_level', 5, 2)->nullable()->comment('Device battery level percentage.');
            $table->decimal('temperature', 5, 2)->nullable()->comment('Device temperature in Celsius.');
            
            $table->timestamps();

            // Add indexes for better query performance
            $table->index(['device_id', 'created_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_levels');
    }
};
