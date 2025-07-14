<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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
    
        $token = $user->createToken('auth-token')->plainTextToken;
    
        return response()->json([
            'success' => true,
            'status' => 200,
            'message' => 'User logged in successfully',
            'data' => [
                'token' => $token,
            ],
        ], 200);
    }
}
