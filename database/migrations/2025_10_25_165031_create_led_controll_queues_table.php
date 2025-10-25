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
        Schema::create('led_controll_queues', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index()->comment('Identifier for the transmitting NodeMCU/ESP8266.');
            $table->enum('command', ['on', 'off'])->comment('The command to send to the device.');
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('led_controll_queues');
    }
};
