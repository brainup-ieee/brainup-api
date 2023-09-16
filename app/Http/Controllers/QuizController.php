<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
//QuizAdded Mail
use Illuminate\Support\Facades\Mail;
use App\Mail\QuizAdded;


class QuizController extends Controller
{
    //Teacher
    public static function create(Request $request){
        $teacher_id = auth()->user()->id;
        $classroom_id = $request->classroom_id;
        // Check if classroom exists and if teacher is the owner
        $classroom = DB::table('classrooms')->where('id', $classroom_id)->where('teacher_id', $teacher_id)->first();
        if(!$classroom){
            return response()->json([
                'status' => 'error',
                'message' => 'Classroom not found'
            ]);
        }
        //decode configs
        $configs = json_decode($request->configs);
        $time = $configs->time;
        $number_of_models = $configs->number_of_models;
        $number_of_questions = $configs->number_of_questions;
        $number_of_choices = $configs->number_of_choices;
        $active = $configs->active;
        $shuffle_questions = $configs->shuffle_questions;
        $instant_results = $configs->instant_results;
        $results_pdf = $configs->results_pdf;
        // $lesson_id = $request->configs->lesson_id;
        // Create quiz
        $quiz = new Quiz;
        $quiz->classroom_id = $classroom_id;
        $quiz->time = $time;
        $quiz->number_of_models = $number_of_models;
        $quiz->number_of_questions = $number_of_questions;
        $quiz->number_of_choices = $number_of_choices;
        $quiz->active = $active;
        $quiz->shuffle_questions = $shuffle_questions;
        $quiz->instant_results = $instant_results;
        $quiz->results_pdf = $results_pdf;
        $quiz->teacher_id = $teacher_id;
        $quiz->save();
        // Upload to Models Table number of models and get each model id
        // Get Questions
        $questions = json_decode($request->questions);
        $models = [];
        for($i = 0; $i < $number_of_models; $i++){
            DB::table('models')->insert([
                'quiz_id' => $quiz->id
            ]);
            $model_id = DB::table('models')->where('quiz_id', $quiz->id)->orderBy('id', 'desc')->first(['id'])->id;
            //Get model from questions array
            $model_questions = $questions[$i];
            // Upload to Questions Table number of questions and get each question id
            for($j = 0; $j < $number_of_questions; $j++){
                DB::table('questions')->insert([
                    'model_id' => $model_id,
                    'question' => $model_questions[$j]->question,
                    'answer' => $model_questions[$j]->answer,
                    'mark' => $model_questions[$j]->mark
                ]);
                $question_id = DB::table('questions')->where('model_id', $model_id)->orderBy('id', 'desc')->first(['id'])->id;
                // Upload to Choices Table number of choices 
                $choices = json_decode($model_questions[$j]->choices);
                for($k = 0; $k < $number_of_choices; $k++){
                    DB::table('choices')->insert([
                        'question_id' => $question_id,
                        'choice' => $choices->$k
                    ]);
                }
            }
        }
        //Send Mail if quiz is active
        if($active == 1){
            $students = DB::table('classrooms_users')->where('classroom_id', $classroom_id)->get();
            foreach($students as $student){
                $student_email = DB::table('users')->where('id', $student->user_id)->first(['email'])->email;
                Mail::to($student_email)->send(new QuizAdded());
            }
        }
      
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz created successfully'
        ]);
    }
    public static function get(){
        $teacher_id = auth()->user()->id;
        //Get quiz_id with classroom name 
        $quizzes = DB::table('quizes')
        ->join('classrooms', 'quizes.classroom_id', '=', 'classrooms.id')
        ->where('quizes.teacher_id', $teacher_id)
        ->select('quizes.id', 'classrooms.name as classroom_name')
        ->get();
        //attemped
        foreach($quizzes as $quiz){
            $quiz->attemped = 2; //static
            $quiz->title = 'Static Electricity';
        }

       return response()->json([
            'status' => 'success',
            'data' => $quizzes
        ]);
    }

}
