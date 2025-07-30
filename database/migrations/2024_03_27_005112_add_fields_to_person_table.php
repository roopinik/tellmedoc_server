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
        Schema::table('person', function (Blueprint $table) {
            $table->boolean("is_primary")->default(false);
            $table->date("date_of_birth")->nullable();
            $table->string("gender")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person', function (Blueprint $table) {
            $table->dropColumn("is_primary");
            $table->dropColumn("date_of_birth");
            $table->dropColumn("gender");
        });
    }
};
