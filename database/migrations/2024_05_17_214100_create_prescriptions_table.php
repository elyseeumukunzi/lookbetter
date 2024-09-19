<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('consultation_id');
            $table->string('ingredient');   //Mineral or Organic
            $table->string('nature');       //Bifocal or Progressive
            $table->string('reaction');     //Clear or Photochromic  (The combination of one prescription must include one of the three lens type)
            $table->string('light_reaction');
            $table->string('comment')->nullable();
            $table->string('status')->nullable()->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
