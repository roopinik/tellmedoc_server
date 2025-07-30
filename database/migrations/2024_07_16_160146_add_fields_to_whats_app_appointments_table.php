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
        Schema::table('whats_app_appointments', function (Blueprint $table) {
            $table->string("rpay_paylink_id")->nullable()->after("payment_refid");
            $table->longText("paylink_response")->nullable()->after("rpay_paylink_id");
            $table->bigInteger("paylink_expires_at")->nullable()->after("paylink_response");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_appointments', function (Blueprint $table) {
            $table->dropColumn("rpay_paylink_id");
            $table->dropColumn("paylink_response");
            $table->dropColumn("paylink_expires_at");
        });
    }
};
