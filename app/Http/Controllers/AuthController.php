<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login'
            ]
        ]);
    }

    public function login(Request $request)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'email',
            'password'
        ]);

        $validator = Validator::make($data, [
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $email = $data['email'];
            $password = $data['password'];

            $token = Auth::attempt([
                'email' => $email,
                'password' => $password
            ]);

            if ($token) {
                $info = Auth::user();
                $info->avatar = url('/media/avatars/' . $info->avatar);
                $array['data'] = $info;
                $array['token'] = $token;
            } else {
                $array['error'] = 'Invalid email or password';
            };
        };

        return $array;
    }

    public function logout()
    {
        $array = [
            'error' => ''
        ];

        Auth::logout();

        return $array;
    }

    public function refresh()
    {
        $array = [
            'error' => ''
        ];

        $token = Auth::refresh();

        if ($token) {
            $info = Auth::user();
            $info->avatar = url('/media/avatars/' . $info->avatar);
            $array['data'] = $info;
            $array['token'] = $token;
        } else {
            $array['error'] = 'Unexpected error! Please try loggin again.';
        };

        return $array;
    }
}
