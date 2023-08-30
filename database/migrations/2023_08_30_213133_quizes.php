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
        Schema::create('quizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('classroom_id');
            $table->foreign('classroom_id')->references('id')->on('classrooms')->onDelete('cascade');
            $table->integer('time');
            $table->integer('number_of_models');
            $table->integer('number_of_questions');
            $table->integer('number_of_choices');
            $table->boolean('active')->default(false);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('instant_results')->default(false);
            $table->intger('results_pdf')->default(0); //1 send once after quiz & Send when teacher  disactiv
        });
        // Models
        Schema::create('models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quiz_id');
            $table->foreign('quiz_id')->references('id')->on('quizes')->onDelete('cascade');
        });
        // Questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->foreign('model_id')->references('id')->on('models')->onDelete('cascade');
            $table->string('question', 1000);
            $table->integer('mark');
        });
        // Choices
        Schema::create('choices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->string('choice', 1000);
            $table->boolean('correct')->default(false);
        });
    }
 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizes');
        Schema::dropIfExists('models');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('choices');
    }
};
