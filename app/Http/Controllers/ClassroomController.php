<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use PDO;

class ClassroomController extends Controller
{
    //Teachers
    public static function create(Request $request)
    {
        $name = $request->name;
        $code = rand(100000, 999999);
        $user_id = auth()->user()->id;
        $classroom = new Classroom;
        $classroom->name = $name;
        $classroom->code = $code;
        $classroom->teacher_id = $user_id;
        $classroom->save();
        return response()->json([
            'status' => 'success',
            'classroom' => [
                'code' => $code,
                'id' => $classroom->id
            ]
        ]);
    }
    public static function get()
    {
        $user_id = auth()->user()->id;
        $classrooms = Classroom::where('teacher_id', $user_id)->get(['id', 'name', 'code']);
        return response()->json([
            'status' => 'success',
            'classrooms' => $classrooms
        ]);
    }
    public static function delete($id){
        $user_id = auth()->user()->id;
        $classroom = Classroom::where('teacher_id', $user_id)->where('id', $id)->first(['id']);
        if($classroom){
            // DB::table('classrooms_users')->where('classroom_id', $id)->delete();
            // DB::table('requests')->where('classroom_id', $id)->delete();
            // DB::table('announcments')->where('classroom_id', $id)->delete();
            DB::table('classrooms')->where('id', $id)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Classroom deleted'
            ]);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Classroom not found'
        ]);
    }
    public static function getById($id)
    {
        $user_id = auth()->user()->id;
        $classroom = Classroom::where('teacher_id', $user_id)->where('id', $id)->first(['id', 'name', 'code']);
        if ($classroom) {
            // Get students
            $students = DB::table('classrooms_users')
                ->join('users', 'users.id', '=', 'classrooms_users.user_id')
                ->where('classrooms_users.classroom_id', $id)
                ->get(['users.id', 'users.name']);
            $classroom->students = $students;
            // Get Requests
            $requests = DB::table('requests')
                ->join('users', 'users.id', '=', 'requests.user_id')
                ->where('requests.classroom_id', $id)
                ->get(['requests.id', 'users.name']);
            $classroom->requests = $requests;
            // Get announcements
            $announcments = DB::table('announcments')
                ->where('classroom_id', $id)
                ->get(['id', 'title', 'description', 'created_at']);
            $classroom->announcments = $announcments;
            return response()->json([
                'status' => 'success',
                'classroom' => $classroom
            ]);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Classroom not found'
        ]);
    }
    public static function approveRequest(request $request){
        $request_id = $request->request_id;
        $classroom_id = DB::table('requests')->where('id', $request_id)->first(['classroom_id']);
        if(!$classroom_id){
            return response()->json([
                'status' => 'failed',
                'message' => 'Request not found'
            ]);
        }
        $classroom_id = $classroom_id->classroom_id;
        $user_id = auth()->user()->id;
        $classroom = Classroom::where('teacher_id', $user_id)->where('id', $classroom_id)->first(['id']);
        $student_id = DB::table('requests')->where('id', $request_id)->first(['user_id'])->user_id;
        if ($classroom) {
            DB::table('classrooms_users')->insert([
                'classroom_id' => $classroom_id,
                'user_id' => $student_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::table('requests')->where('id', $request_id)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Request approved'
            ]);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Request is invalid'
        ]);
    }
    public static function rejectRequest(request $request){
        $request_id = $request->request_id;
        $classroom_id = DB::table('requests')->where('id', $request_id)->first(['classroom_id']);
        if(!$classroom_id){
            return response()->json([
                'status' => 'failed',
                'message' => 'Request not found'
            ]);
        }
        $classroom_id = $classroom_id->classroom_id;
        $user_id = auth()->user()->id;
        $classroom = Classroom::where('teacher_id', $user_id)->where('id', $classroom_id)->first(['id']);
        if ($classroom) {
            DB::table('requests')->where('id', $request_id)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Request rejected'
            ]);
        }
        return response()->json([
            'status' => 'failed',
            'message' => 'Request is invalid'
        ]);
    }
    //Students
    public static function join(Request $request)
    {
        $code = $request->code;
        $user_id = auth()->user()->id;
        $classroom = Classroom::where('code', $code)->first(['id', 'teacher_id']);
        // Check if classroom exists
        if (!$classroom) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Classroom not found'
            ]);
        }
        // Check if user is already in classroom
        $isInClassroom = DB::table('classrooms_users')
            ->where('classroom_id', $classroom->id)
            ->where('user_id', $user_id)
            ->first();
        if ($isInClassroom) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You are already in this classroom'
            ]);
        }
        // Check if user has already requested to join
        $hasRequested = DB::table('requests')
            ->where('classroom_id', $classroom->id)
            ->where('user_id', $user_id)
            ->first();
        if ($hasRequested) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You have already requested to join this classroom'
            ]);
        }
        // Check if teacher blocked user
        $teacher_id = $classroom->teacher_id;
        $isBlocked = DB::table('blocks')
            ->where('teacher_id', $teacher_id)
            ->where('student_id', $user_id)
            ->first();
        if ($isBlocked) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You have been blocked by the teacher'
            ]);
        }
        // Add request
        DB::table('requests')->insert([
            'classroom_id' => $classroom->id,
            'user_id' => $user_id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Request sent'
        ]);
    }
    // Get Classrooms
    public static function S_get(){
        $user_id = auth()->user()->id;
        //Get classrooms [id/name/name of teacher where classroom belongs to]
        $classrooms = DB::table('classrooms_users')
            ->join('classrooms', 'classrooms.id', '=', 'classrooms_users.classroom_id')
            ->join('users', 'users.id', '=', 'classrooms.teacher_id')
            ->where('classrooms_users.user_id', $user_id)
            ->get(['classrooms.id', 'classrooms.name', 'users.name as teacher_name']);
        return response()->json([
            'status' => 'success',
            'classrooms' => $classrooms
        ]);
    }
}
