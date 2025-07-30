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
        Schema::table('clients', function (Blueprint $table) {
            $table->string("flow_template_id_kn")->default("telmedoc_appointment_flow_kn");
            $table->longText("whatsapp_header");
            $table->longText("whatsapp_footer");
            $table->longText("appointment_instructions");
            $table->longText("whatsapp_header_kn");
            $table->longText("whatsapp_footer_kn");
            $table->longText("appointment_instructions_kn");
            $table->string("msg91_key")->nullable();
            $table->string("min_30_rem_templtid")->default("67496ab4d6fc053d3f1ef004");
            $table->string("daily_rem_templtid")->default("674967c9d6fc050e6f758472");
            $table->string("follow_up_templtid")->default("676e4d01d6fc052eb43c5b92");
            $table->string("cancel_templtid")->default("67496b2bd6fc053936007493");
            $table->string("reschedule_templtid")->default("676e4c64d6fc051a9f3cbea3");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn("msg91_key");
            $table->dropColumn("min_30_rem_templtid");
            $table->dropColumn("daily_rem_templtid");
            $table->dropColumn("follow_up_templtid");
            $table->dropColumn("cancel_templtid");
            $table->dropColumn("reschedule_templtid");
            $table->dropColumn("flow_template_id_kn");
            $table->dropColumn("whatsapp_header");
            $table->dropColumn("whatsapp_footer");
            $table->dropColumn("appointment_instructions");
            $table->dropColumn("whatsapp_header_kn");
            $table->dropColumn("whatsapp_footer_kn");
            $table->dropColumn("appointment_instructions_kn");
        });
    }
};
