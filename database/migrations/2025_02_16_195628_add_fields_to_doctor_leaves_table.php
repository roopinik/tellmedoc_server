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
        Schema::table('doctor_leaves', function (Blueprint $table) {
            $table->dropColumn("leave_start");
            $table->dropColumn("leave_end");
            // $table->dateTime("date")->nullable();
            $table->dropColumn("day_time");
            $table->boolean("allday")->default(false);
            $table->json("timeranges")->nullable();
            $table->bigInteger("hospital_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_leaves', function (Blueprint $table) {
            $table->dropColumn("allday");
            $table->dropColumn("timeranges");
        });
    }
};
