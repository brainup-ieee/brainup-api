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
        Schema::table('lessons_data', function (Blueprint $table) {
            $table->dropForeign('lessons_data_lesson_id_foreign');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons_data', function (Blueprint $table) {
            $table->dropForeign('lessons_data_lesson_id_foreign');
            $table->foreign('lesson_id')->references('id')->on('lessons');
        });
    }
};
