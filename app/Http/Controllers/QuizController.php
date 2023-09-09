<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuizController extends Controller
{
    public static function create(){
        return response()->json([
            'status' => 'success',
            'message' => 'Quiz created successfully'
        ]);
    }
}
