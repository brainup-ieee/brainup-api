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
        Schema::table('quizes', function (Blueprint $table) {
            //add foreign key user_id referencing users table
            $table->unsignedBigInteger('teacher_id')->after('id');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizes', function (Blueprint $table) {
            $table->dropColumn('teacher_id');
        });
    }
};
