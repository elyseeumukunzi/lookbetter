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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('receptionist_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('doctor_name');
            $table->unsignedBigInteger('insurance_id');
            $table->unsignedBigInteger('type_of_consultations_id');
            $table->string('dates');
            $table->string('RDVsphere'); //Right Distant Vision Sphere
            $table->string('RDVcylinder'); //Right Distant Vision Cylinder
            $table->string('RDVaxis');    //Right Distant Vision Axis
            $table->string('LDVsphere');  //Left Distant Vision Sphere
            $table->string('LDVcylinder');  //Left Distant Vision Cylinder
            $table->string('LDVaxis');      //Left Distant Vision Axis
            $table->string('RNV');          //Right Near Vision
            $table->string('LNV');          //Left Near Vision   (These are called the Codes that have their own technical meaning  implemented by @UMUKUNZI Elysee) 
            $table->string('distant_comment')->nullable();
            $table->string('near_comment')->nullable();
            $table->string('prescription_status')->nullable();
            $table->integer('status')->nullable()->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
