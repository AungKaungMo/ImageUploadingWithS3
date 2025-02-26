<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\ApiResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('AUTHENTICATION')->plainTextToken;
            $success['name'] =  $user->name;
            $success['id'] =  $user->id;
            $success['role'] = $user->getRoleNames();
            $success['permissions'] = $user->getPermissionsViaRoles()->pluck('name');

            return ApiResponse::success($success, 'User login successfully.', 200);
        } else {
            return ApiResponse::error('Email or Password Invalid', 400);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return ApiResponse::success(null, 'User logout successfully.', 200);
    }
}
