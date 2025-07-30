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
        Schema::create('whats_app_appointments', function (Blueprint $table) {
            $table->id();
            $table->string("patient_name")->nullable();
            $table->string("booking_whatsapp_number");
            $table->string("booking_contact_number")->nullable();
            $table->string("payment_url")->nullable();
            $table->string("payment_status")->nullable();
            $table->string("payment_refid")->nullable();
            $table->string("video_conf_url")->nullable();
            $table->integer("amount")->nullable();
            $table->bigInteger("doctor_id")->nullable();
            $table->bigInteger("client_id");
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_appointments');
    }
};
