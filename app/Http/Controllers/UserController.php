<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'create'
            ]
        ]);

        $this->loggedUser = Auth::user();
    }

    public function create(Request $request)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation'
        ]);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:50|min:2',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $name = $data['name'];
            $email = $data['email'];
            $password = $data['password'];

            do {
                $publicId = $this->generateUuid();
            } while (User::where('public_id', $publicId)->count() !== 0);

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $newUser = new User();
            $newUser->public_id = $publicId;
            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->password = $hash;
            $newUser->save();

            $token = Auth::attempt(
                [
                    'email' => $email,
                    'password' => $password
                ]
            );

            if ($token) {
                $info = Auth::user();
                $info->avatar = url('/media/avatars/' . $info->avatar);
                $array['data'] = $info;
                $array['token'] = $token;
            } else {
                $array['error'] = 'Unexpected error. Please try logging again!';
            };
        };

        return $array;
    }

    public function read($id = false)
    {
        $array = [
            'error' => ''
        ];

        if($id){
            $validator = Validator::make(['public_id' => $id], [
                'public_id' => 'uuid|exists:users'
            ]);
        } else {
            $id = $this->loggedUser['public_id'];
        };

        if (isset($validator) && $validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $user = User::where('public_id', $id)->first();

            $me = ($user['public_id'] === $this->loggedUser['public_id']) ?
                true : false;

            if($me === false){
                unset($user['email']);
                unset($user['telephone']);
            };

            $array['info'] = $user;
        };

        return $array;
    }

    private function generateUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
