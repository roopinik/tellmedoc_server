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
            $table->integer("online_slot_duration")->nullable();
            $table->integer("offline_slot_duration")->nullable();
            $table->json("online_slots")->nullable();
            $table->json("offline_slots")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filament_users', function (Blueprint $table) {
            $table->dropColumn("online_slot_duration");
            $table->dropColumn("offline_slot_duration");
            $table->dropColumn("online_slots");
            $table->dropColumn("offline_slots");
        });
    }
};
