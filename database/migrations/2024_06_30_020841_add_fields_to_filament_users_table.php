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
            $table->string("license_id")->nullable();
            $table->string("whats_app_number")->nullable();
            $table->string("receptionist_whatsapp_number")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filament_users', function (Blueprint $table) {
            $table->dropColumn("license_id");
            $table->dropColumn("whats_app_number");
            $table->dropColumn("receptionist_whatsapp_number");
        });
    }
};
