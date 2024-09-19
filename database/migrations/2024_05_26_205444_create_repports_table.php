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
        Schema::create('repports', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('total_sales');
            $table->string('cash_at_hand')->nullable();
            $table->string('cash_at_partners')->nullable();
            $table->string('total_expence')->nullable();
            $table->string('total_tax')->nullable();
            $table->string('from_date')->nullable();
            $table->string('to_date')->nullable();
            $table->string('date')->nullable();
            $table->string('sms_sent_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repports');
    }
};
