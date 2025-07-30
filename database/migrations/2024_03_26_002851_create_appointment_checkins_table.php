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
        Schema::create('appointment_checkins', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->bigInteger('appointment_id');
            $table->bigInteger('doctor_id');
            $table->bigInteger('client_id');
            $table->dateTime('checked_in_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('checkin_status')->nullable();
            $table->string('call_status');
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_checkins');
    }
};
