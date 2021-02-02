<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserFavorite;
use App\Models\Barber;
use App\Models\UserAppointment;
use App\Models\BarberService;
use DateTime;
use Intervention\Image\ImageManager;

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
            $name = ucwords($data['name']);
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

        if ($id) {
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

            $user->avatar = url('/media/avatars/' . $user->avatar);

            $me = ($user['public_id'] === $this->loggedUser['public_id']) ?
                true : false;

            if ($me === false) {
                unset($user['email']);
                unset($user['telephone']);
            };

            $array['info'] = $user;
        };

        return $array;
    }

    public function toggleFavorite(Request $request)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'barber'
        ]);

        $validator = Validator::make($data, [
            'barber' => 'required|exists:barbers,public_id'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $barberId = $data['barber'];

            $favorite = UserFavorite::where('id_user', $this->loggedUser['public_id'])
                ->where('id_barber', $barberId)->first();

            if ($favorite) {
                $array['data'] = $favorite;
                $favorite->have = false;

                $favorite->delete();
            } else {
                do {
                    $publicId = $this->generateUuid();
                } while (UserFavorite::where('public_id', $publicId)->count() !== 0);

                $newFavorite = new UserFavorite();
                $newFavorite->public_id = $publicId;
                $newFavorite->id_user = $this->loggedUser['public_id'];
                $newFavorite->id_barber = $barberId;
                $newFavorite->save();

                $newFavorite->have = true;

                $array['data'] = $newFavorite;
            };
        };

        return $array;
    }

    public function getFavorites()
    {
        $array = [
            'error' => '',
            'list' => []
        ];

        $favs = UserFavorite::where('id_user', $this->loggedUser['public_id'])
            ->get();

        foreach ($favs as $fav) {
            $barber = Barber::where('public_id', $fav['id_barber'])->first();
            $barber->avatar = url('/media/barber-avatars/' . $barber->avatar);
            $array['list'][] = $barber;
        };

        return $array;
    }

    public function getAppointments(Request $request)
    {
        $array = [
            'error' => '',
            'list' => []
        ];

        $data = $request->only([
            'now'
        ]);

        $validator = Validator::make($data, [
            'now' => 'required|date'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $sentNow = new DateTime(date('Y-m-d H:i:s', strtotime($data['now'])));
            $minDate = new DateTime(gmdate('Y-m-d H:i:s', strtotime('-12 hours')));
            $maxDate = new DateTime(gmdate('Y-m-d H:i:s', strtotime('+14 hours')));

            if ($sentNow < $minDate || $sentNow > $maxDate) {
                $array['error'] = 'You may not get your appointments if you are living in the future (or past)';
            } else {
                $apps = UserAppointment::where('id_user', $this->loggedUser['public_id'])
                    ->where('ap_datetime', '>=', $sentNow->format('Y-m-d H:i:s'))
                    ->orderBy('ap_datetime', 'ASC')->get();

                foreach ($apps as $app) { {
                        $barber = Barber::where('public_id', $app['id_barber'])
                            ->first();
                        $barber->avatar = url('/media/barber-avatars/' . $barber->avatar);

                        $service = BarberService::where('public_id', $app['id_service'])
                            ->first();

                        if ($service->photo) {
                            $service->photo = url('/media/uploads/' . $service->photo);
                        };

                        $array['list'][] = [
                            'public_id' => $app->public_id,
                            'datetime' => $app->ap_datetime,
                            'barber' => $barber,
                            'service' => $service
                        ];
                    };
                };
            };
        };

        return $array;
    }

    public function update(Request $request)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation',
            'telephone'
        ]);

        $validator = Validator::make($data, [
            'name' => 'string|min:2|max:50',
            'email' => 'string|email|max:50|unique:users',
            'password' => 'string|min:8|confirmed',
            'telephone' => 'digits:11|numeric'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $name = ucwords($data['name'] ?? '');
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';
            $telephone = $data['telephone'] ?? '';

            $user = User::where('public_id', $this->loggedUser['public_id'])
                ->first();

            if ($name) {
                $user->name = $name;
            };

            if ($email) {
                $user->email = $email;
            };

            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $user->password = $hash;
            };

            if ($telephone) {
                $user->telephone = $telephone;
            };

            $user->save();

            $user->avatar = url('/media/avatars/' . $user->avatar);

            $array['data'] = $user;
        };

        return $array;
    }

    public function updateAvatar(Request $request)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'avatar'
        ]);

        $validator = Validator::make($data, [
            'avatar' => 'required|image|mimes:png,jpg,jpeg,webp|max:10240'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $avatar = $data['avatar'];

            $avatarName = $this->loggedUser['public_id'] . '.webp';
            $dest = public_path('/media/avatars/') . $avatarName;

            $manager = new ImageManager();

            $img = $manager->make($avatar->getRealPath())->fit(300, 300);
            $img->save($dest);

            $user = User::where('public_id', $this->loggedUser['public_id'])
                ->first();
            $user->avatar = $avatarName;
            $user->save();
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
