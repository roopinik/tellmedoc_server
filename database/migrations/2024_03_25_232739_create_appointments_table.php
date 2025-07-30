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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->date("appointment_date");
            $table->string("schedule_time");
            $table->bigInteger("client_id");
            $table->string("appointment_type");
            $table->string("appointment_status");
            $table->bigInteger("doctor_id");
            $table->integer("total_amount")->nullable();
            $table->integer("video_cost")->nullable();
            $table->string("abha_id")->nullable();
            $table->string("mrn")->nullable();
            $table->string("person_id")->nullable();
            $table->string("payment_id")->nullable();
            $table->string("refund_ref_id")->nullable();
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('appointments');
    }
};
