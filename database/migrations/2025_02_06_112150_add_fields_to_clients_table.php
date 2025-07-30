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
        Schema::table('clients', function (Blueprint $table) {
            $table->string("health_care_type")->nullable();
            $table->boolean("enable_payment")->nullable();
            $table->string("flow_template_id")->default("telmedoc_appointment_flow");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn("health_care_type");
            $table->dropColumn("enable_payment");
            $table->dropColumn("flow_template_id");
        });
    }
};
