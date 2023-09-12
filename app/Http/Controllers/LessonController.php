<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\LessonData;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    public static function create(Request $request){
        $classroom_id = $request->classroom_id;
        $teacher_id = auth()->user()->id;
        // Check if classroom exists and if teacher is the owner
        $classroom = DB::table('classrooms')->where('id', $classroom_id)->where('teacher_id', $teacher_id)->first();
        if(!$classroom){
            return response()->json([
                'status' => 'error',
                'message' => 'Classroom not found'
            ]);
        }
        $lesson_name = $request->name;
        $lesson = new Lesson;
        $lesson->classroom_id = $classroom_id;
        $lesson->name = $lesson_name;
        $lesson->teacher_id = $teacher_id;
        $lesson->save();
        // Check if there is video
        if($request->hasFile('video')){
            $video = $request->file('video');
            //add unique name to video using timestamp
            $video_name = time().'_'.$video->getClientOriginalName();
            //move video to uploads folder
            $video->move(public_path('uploads/lessons/videos'), $video_name);
            $lesson_data = new LessonData;
            $lesson_data->lesson_id = $lesson->id;
            $lesson_data->data_type = 1;
            $lesson_data->name = $video_name;
            $lesson_data->save();
        }
        // Check if there is pdf
        if($request->hasFile('pdf')){
            $pdf = $request->file('pdf');
            //add unique name to pdf using timestamp
            $pdf_name = time().'_'.$pdf->getClientOriginalName();
            //move pdf to uploads folder
            $pdf->move(public_path('uploads/lessons/pdfs'), $pdf_name);
            $lesson_data = new LessonData;
            $lesson_data->lesson_id = $lesson->id;
            $lesson_data->data_type = 2;
            $lesson_data->name = $pdf_name;
            $lesson_data->save();
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Lesson created successfully'
        ]);
    }
    public static function get($room_id){
        $teacher_id = auth()->user()->id;
        $lessons = Lesson::where('teacher_id', $teacher_id)->where('classroom_id',$room_id)->get(['name', 'id','created_at']);
        if(!$lessons){
            return response()->json([
                'status' => 'error',
                'message' => 'No Lessons found'
            ]);
        }
        // Check if there is video or pdf
        foreach($lessons as $lesson){
            $lesson_data = LessonData::where('lesson_id', $lesson->id)->get(['data_type', 'name']);
            $lesson->data = $lesson_data;
        }
        return response()->json([
            'status' => 'success',
            'lessons' => $lessons
        ]);
    }
    public static function delete($lesson_id){
        $teacher_id = auth()->user()->id;
        $lesson = Lesson::where('teacher_id', $teacher_id)->where('id',$lesson_id)->first();
        if(!$lesson){
            return response()->json([
                'status' => 'error',
                'message' => 'Lesson not found'
            ]);
        }
        // Delete lesson data
        $lesson_data = LessonData::where('lesson_id', $lesson_id)->get();
        foreach($lesson_data as $data){
            $data->delete();
        }
        $lesson->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Lesson deleted successfully'
        ]);
    }
    //Students
    public static function getStudentLessons($room_id){
        $lessons = Lesson::where('classroom_id', $room_id)->get(['name', 'id','created_at']);
        // Check if there is video or pdf
        foreach($lessons as $lesson){
            $lesson_data = LessonData::where('lesson_id', $lesson->id)->get(['data_type', 'name']);
            $lesson->data = $lesson_data;
        }
        return response()->json([
            'status' => 'success',
            'lessons' => $lessons
        ]);
    }
}
