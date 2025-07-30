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
        Schema::table('filament_users', function (Blueprint $table) {
           $table->date("working_since")->nullable();
           $table->bigInteger("online_appointment_cost")->nullable()->unsigned();
           $table->bigInteger("offline_appointment_cost")->nullable()->unsigned();
           $table->date("date_of_birth")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filament_users', function (Blueprint $table) {
            $table->dropColumn('working_since');
            $table->dropColumn('online_appointment_cost');
            $table->dropColumn('offline_appointment_cost');
            $table->dropColumn("date_of_birth");
        });
    }
};
