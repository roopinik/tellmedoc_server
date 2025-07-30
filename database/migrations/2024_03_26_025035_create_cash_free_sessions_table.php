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
        Schema::create('cash_free_sessions', function (Blueprint $table) {
            $table->id();
            $table->string("cf_order_id")->nullable();
            $table->string("order_expiry_time")->nullable();
            $table->string("order_id")->nullable();
            $table->string("order_note")->nullable();
            $table->string("type")->nullable();
            $table->longText("response")->nullable();
            $table->longText("payment_session_id")->nullable();
            $table->bigInteger("payment_entity_id")->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_free_sessions');
    }
};
