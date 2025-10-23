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

            $table->float('level_cm')->comment('The measured water level in centimeters.');
            
            $table->json('raw_data')->nullable()->comment('Optional raw data payload from the sensor.');
            
            $table->timestamps();
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
