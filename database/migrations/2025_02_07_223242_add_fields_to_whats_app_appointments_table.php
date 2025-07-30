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
        Schema::table('whats_app_appointments', function (Blueprint $table) {
            $table->bigInteger("hospital_id")->nullable();
            $table->string("appointment_end_time")->nullable();
            $table->string("alternate_mobile")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_appointments', function (Blueprint $table) {
            $table->dropColumn("hospital_id");
            $table->dropColumn("appointment_end_time");
            $table->dropColumn("alternate_mobile");
        });
    }
};
