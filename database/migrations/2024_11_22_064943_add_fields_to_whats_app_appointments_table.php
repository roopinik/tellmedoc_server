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
            $table->dropColumn('is_cancelled');
            $table->dropColumn('is_completed');
            $table->dropColumn('booking_contact_number');
            $table->tinyInteger('reminder_type')->default(0);
            $table->boolean('30min_reminder_sent')->default(0);
            $table->boolean('daily_reminder_sent')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whats_app_appointments', function (Blueprint $table) {
            $table->dropColumn('30min_reminder_sent');
            $table->dropColumn('daily_reminder_sent');
            $table->dropColumn('reminder_type');
            $table->string('booking_contact_number')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_cancelled')->default(false);
        });
    }
};
