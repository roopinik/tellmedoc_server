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
            $table->renameColumn('online_slots', 'appointment_slots');
            $table->renameColumn('online_slot_duration', 'appointment_slot_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filament_users', function (Blueprint $table) {
            $table->renameColumn('appointment_slots', 'online_slots');
            $table->renameColumn('appointment_slot_duration', 'online_slot_duration');
        });
    }
};
