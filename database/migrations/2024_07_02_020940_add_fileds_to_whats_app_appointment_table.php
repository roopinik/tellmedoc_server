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
            $table->date("appointment_date")->nullable()->after("booking_contact_number");
            $table->string("appointment_time")->nullable()->after("booking_contact_number");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_appointments', function (Blueprint $table) {
            $table->dropColumn("appointment_date");
            $table->dropColumn("appointment_time");
        });
    }
};
