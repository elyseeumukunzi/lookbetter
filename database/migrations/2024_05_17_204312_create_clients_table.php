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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('insurance_id');
            $table->string ('firstname');
            $table->string ('lastname');
            $table->string ('sex');
            $table->string ('nid')->nullable();
            $table->string ('dob');
            $table->string ('province');
            $table->string ('district');
            $table->string ('sector');
            $table->string ('cell');
            $table->string ('village')->nullable();
            $table->string ('phonenumber')->nullable();
            $table->string ('cardnumber')->nullable();
            $table->string ('affiliatesociety')->nullable();
            $table->string ('relationship')->nullable();
            $table->string ('mainmember')->nullable();      
            $table->integer ('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
