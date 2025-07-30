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
        Schema::table('cash_free_sessions', function (Blueprint $table) {
            $table->uuid("uuid");
            $table->bigInteger("client_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_free_sessions', function (Blueprint $table) {
            $table->dropColumn("uuid");
            $table->dropColumn("client_id");
        });
    }
};
