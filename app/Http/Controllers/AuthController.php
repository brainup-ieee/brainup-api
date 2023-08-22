<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordEmail;
use App\Mail\ConfirmEmail;
use App\Mail\WelcomeEmail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        //Validate data
        //Check if email or username already exists
        $user = User::where('email', $request->email)->orWhere('username', $request->username)->first();
        if ($user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Email or username already exists'
            ]);
        }
        //phone number is valid
        if (strlen($request->phone_number) != 11) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Phone number is invalid'
            ]);
        }
        //Save user in database
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'user-type' => $request->user_type,
            'phone-number' => $request->phone_number,
            'password' => password_hash($request->password, PASSWORD_DEFAULT)
        ]);
        $user->save();
        //Send email to user
        try {
            Mail::to($request->email)->send(new WelcomeEmail($request->name));
        } catch (\Throwable $th) {
            //throw $th;
        }
        //Return response
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully'
        ]);
    }
    public function login(Request $request)
    {
        $user = $request->user;
        $password = $request->password;
        //Check if user exists
        $user = User::where('email', $user)->orWhere('username', $user)->first();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Data'
            ]);
        }
        //Check if password is correct
        if (!password_verify($password, $user->password)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid Data'
            ]);
        }
        return response()->json([
            'status' => 'success',
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user-type' => $user->{'user-type'}
        ]);
    }
    public function forgotPassword(Request $request)
    {
        $email = $request->email;
        if (User::where('email', $email)->exists()) {
            ///Create password reset token
            $token = bin2hex(random_bytes(64));
            $code = rand(100000, 999999);
            ///Save token to password_reset_tokens table & remove old tokens
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $token,
                'code' => $code,
                'created_at' => now()
            ]);

            //Send reset password email
            try {
                Mail::to($email)->send(new ResetPasswordEmail($token, $code));
            } catch (\Throwable $th) {
                //throw $th;
            }
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'success']);
        }
    }
    public function verifyresetPassword($token)
    {
        if (DB::table('password_reset_tokens')->where('token', $token)->exists()) {
            //Check if token is expired [passed 15mins]
            $created_at = DB::table('password_reset_tokens')->where('token', $token)->first()->created_at;
            $created_at = strtotime($created_at);
            $now = strtotime(now());
            $diff = $now - $created_at;
            if ($diff > 900) {
                return response()->json(['status' => 'failed', 'message' => 'Token expired!']);
            }
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalid token!']);
        }
    }
    public function verifyresetPasswordCode(Request $request){
        $code = $request->code;
        if (DB::table('password_reset_tokens')->where('code', $code)->exists()) {
            //Check if code is expired [passed 15mins]
            $created_at = DB::table('password_reset_tokens')->where('code', $code)->first()->created_at;
            $created_at = strtotime($created_at);
            $now = strtotime(now());
            $diff = $now - $created_at;
            if ($diff > 900) {
                return response()->json(['status' => 'failed', 'message' => 'Code expired!']);
            }
            return response()->json(['status' => 'success', 'token' => DB::table('password_reset_tokens')->where('code', $code)->first()->token]);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalid Code!']);
        }
    }
    public function resetPassword(Request $request)
    {
        $token = $request->token;
        $password = password_hash(strip_tags($request->password), PASSWORD_DEFAULT);
        if (DB::table('password_reset_tokens')->where('token', $token)->exists()) {
            //Check if token is expired [passed 15mins]
            $created_at = DB::table('password_reset_tokens')->where('token', $token)->first()->created_at;
            $created_at = strtotime($created_at);
            $now = strtotime(now());
            $diff = $now - $created_at;
            if ($diff > 900) {
                return response()->json(['status' => 'failed', 'message' => 'Token expired!']);
            }
            $email = DB::table('password_reset_tokens')->where('token', $token)->first()->email;
            DB::table('password_reset_tokens')->where('token', $token)->delete();
            DB::table('users')->where('email', $email)->update([
                'password' => $password
            ]);
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalid token!']);
        }
    }
    //Confirm email method
    public function confirmEmail(Request $request)
    {
        $email = $request->email;
        if (User::where('email', $email)->exists()) {
            $user = User::where('email', $email)->first();
            if ($user->email_verified_at == null) {
                ///Create email confirm token
                $token = bin2hex(random_bytes(64));
                $code = rand(100000, 999999);
                ///Save token to email_confirm_tokens table & remove old tokens
                DB::table('email_confirmation_tokens')->where('email', $email)->delete();
                DB::table('email_confirmation_tokens')->insert([
                    'email' => $email,
                    'token' => $token,
                    'code' => $code,
                    'created_at' => now()
                ]);
                //Send confirm email
                try {
                    Mail::to($email)->send(new ConfirmEmail($token, $code));
                } catch (\Throwable $th) {
                    //throw $th;
                }
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'failed', 'message' => 'Email already verified!']);
            }
        }
        return response()->json(['status' => 'failed', 'message' => 'Invalid email!']);
    }
    //Verify confirm email token
    public function verifyConfirmEmail($token)
    {
        if (DB::table('email_confirmation_tokens')->where('token', $token)->exists()) {
            //Check if token is expired [passed 15mins]
            $created_at = DB::table('email_confirmation_tokens')->where('token', $token)->first()->created_at;
            $created_at = strtotime($created_at);
            $now = strtotime(now());
            $diff = $now - $created_at;
            if ($diff > 900) {
                return response()->json(['status' => 'failed', 'message' => 'Token expired!']);
            }
            //Confirm email
            $email = DB::table('email_confirmation_tokens')->where('token', $token)->first()->email;
            DB::table('email_confirmation_tokens')->where('token', $token)->delete();
            DB::table('users')->where('email', $email)->update([
                'email_verified_at' => now()
            ]);
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalid token!']);
        }
    }
    //Verify confirm email code
    public function verifyConfirmEmailCode(Request $request)
    {
        $code = $request->code;
        if (DB::table('email_confirmation_tokens')->where('code', $code)->exists()) {
            //Check if code is expired [passed 15mins]
            $created_at = DB::table('email_confirmation_tokens')->where('code', $code)->first()->created_at;
            $created_at = strtotime($created_at);
            $now = strtotime(now());
            $diff = $now - $created_at;
            if ($diff > 900) {
                return response()->json(['status' => 'failed', 'message' => 'Code expired!']);
            }
            $email = DB::table('email_confirmation_tokens')->where('code', $code)->first()->email;
            DB::table('email_confirmation_tokens')->where('code', $code)->delete();
            DB::table('users')->where('email', $email)->update([
                'email_verified_at' => now()
            ]);
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'failed', 'message' => 'Invalid Code!']);
        }
    }
}
