<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Logout successful'
        ])->withCookie(cookie()->forget('sanctum_token'));
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Bad Request',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        User::create($validator->validated());
    
        return response()->json([
            'success' => true,
            'status' => 201,
            'message' => 'User created successfully',
            'data' => [],
        ], 201);
    }

    public function login(Request $request){
       try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Bad Request',
                'errors' => $validator->errors(),
            ], 400);
        }
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'status' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }
    
        $token = $user->createToken('auth_token')->plainTextToken;

        $cookie = cookie('sanctum_token', $token, 60 * 24 * 7); // 7 days

        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'Login successful',
            'user' => $user,
        ])->withCookie($cookie);
       } catch (\Throwable $th) {
        return response()->json([
            'success' => false,
            'status' => 500,
            'message' => 'Internal Server Error',
            'errors' => $th->getMessage(),
        ], 500);
       }
    }
}
