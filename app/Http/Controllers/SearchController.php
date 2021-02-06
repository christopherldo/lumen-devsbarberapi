<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Barber;

class SearchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function search(Request $request)
    {
        $array = [
            'error' => '',
            'list' => []
        ];

        $data = $request->only([
            'q'
        ]);

        $validator = Validator::make($data, [
            'q' => 'required|string'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $q = $data['q'];

            $barbers = Barber::where('name', 'LIKE', '%' . $q . '%')->get();

            foreach ($barbers as $key => $value) {
                $barbers[$key]['avatar'] = url('/media/barber-avatars/' . $barbers[$key]['avatar']);
            };

            $array['list'] = $barbers;
        };

        return $array;
    }
}
