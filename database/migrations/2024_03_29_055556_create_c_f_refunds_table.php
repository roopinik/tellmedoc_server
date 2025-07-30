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
        Schema::create('c_f_refunds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("client_id");
            $table->bigInteger("entity_id");
            $table->string("module");
            $table->string("refund_status")->nullable();
            $table->string("refund_response")->nullable();
            $table->string("refund_notes")->nullable();
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
        Schema::dropIfExists('c_f_refunds');
    }
};
