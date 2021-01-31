<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Barber;
use App\Models\BarberAvailability;
use App\Models\BarberPhoto;
use App\Models\BarberService;
use App\Models\BarberTestimonial;
use App\Models\UserAppointment;
use App\Models\UserFavorite;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BarberController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = Auth::user();
    }

    public function createRandom()
    {
        $array = [
            'error' => ''
        ];

        for ($q = 0; $q < 20; $q++) {
            $names = [
                'Bonieky',
                'Paulo',
                'Pedro',
                'Amanda',
                'Leticia',
                'Gabriel'
            ];

            $lastNames = [
                'Silva',
                'Lacerda',
                'Diniz',
                'Alvaro',
                'Souza',
                'Godin'
            ];

            $passwords = [
                'C8Jk^S!2',
                '75w5bT#j',
                'ynM&10Tu',
                '^0NXJ6t6',
                'hf9!W3u@',
                '2#mQ4wI8'
            ];

            $services = [
                'Corte',
                'Pintura',
                'Aparação',
                'Enfeite'
            ];

            $services2 = [
                'Cabelo',
                'Unha',
                'Pernas',
                'Sobrancelhas'
            ];

            $testimonials = [
                'Morbi leo erat, fringilla varius tincidunt sit amet, pharetra vitae eros. Aenean porta, libero non auctor aliquet, nibh est congue.',
                'Vestibulum id felis sit amet augue lacinia pellentesque non at est. Cras blandit ipsum vel dolor laoreet, interdum mollis nisl.',
                'Pellentesque dictum dolor id enim consectetur ultricies et quis eros. Nulla facilisi. Aliquam sodales semper felis et pulvinar. Duis sit.',
                'Morbi quis augue quis orci faucibus rutrum nec ut ex. Donec ac risus id massa suscipit iaculis et a ante.',
                'In nec magna ut nisl pretium dictum vel eget lorem. Donec sit amet convallis lectus, id faucibus enim. Mauris id.'
            ];

            $hash = password_hash($passwords[rand(0, count($names) - 1)], PASSWORD_DEFAULT);

            do {
                $publicId = $this->generateUuid();
            } while (Barber::where('public_id', $publicId)->count() !== 0);

            $newBarber = new Barber();
            $newBarber->public_id = $publicId;
            $newBarber->name = $names[rand(0, count($names) - 1)] . ' ' .
                $lastNames[rand(0, count($lastNames) - 1)];
            $newBarber->avatar = $publicId . '.webp';
            $newBarber->stars = rand(3, 4) . '.' . rand(0, 9);
            $newBarber->latitude = '-11.17' . rand(0, 9) . '840';
            $newBarber->longitude = '-61.90' . rand(0, 9) . '101';
            $newBarber->password = $hash;
            $newBarber->save();

            for ($w = 0; $w < 4; $w++) {
                do {
                    $publicId = $this->generateUuid();
                } while (BarberPhoto::where('public_id', $publicId)->count() !== 0);

                $newBarberPhoto = new BarberPhoto();
                $newBarberPhoto->public_id = $publicId;
                $newBarberPhoto->id_barber = $newBarber->public_id;
                $newBarberPhoto->url = rand(1, 5) . '.webp';
                $newBarberPhoto->save();
            };

            $ns = rand(4, 6);

            for ($w = 0; $w < $ns; $w++) {
                do {
                    $publicId = $this->generateUuid();
                } while (BarberService::where('public_id', $publicId)->count() !== 0);

                $newBarberService = new BarberService();
                $newBarberService->public_id = $publicId;
                $newBarberService->id_barber = $newBarber->public_id;
                $newBarberService->name = $services[rand(0, count($services) - 1)] .
                    ' de ' . $services2[rand(0, count($services) - 1)];
                $newBarberService->price = rand(10, 99) . '.' . rand(0, 99);
                $newBarberService->save();
            };

            for ($w = 0; $w < 3; $w++) {
                do {
                    $publicId = $this->generateUuid();
                } while (BarberTestimonial::where('public_id', $publicId)->count() !== 0);

                $newBarberTestimonial = new BarberTestimonial();
                $newBarberTestimonial->public_id = $publicId;
                $newBarberTestimonial->id_barber = $newBarber->public_id;
                $newBarberTestimonial->id_user = $this->generateUuid();
                $newBarberTestimonial->name = $names[rand(0, count($names) - 1)] .
                    ' ' . $lastNames[rand(0, count($lastNames) - 1)];
                $newBarberTestimonial->rate = rand(3, 4) . '.' . rand(0, 9);
                $newBarberTestimonial->body = $testimonials[rand(0, count($testimonials) - 1)];
                $newBarberTestimonial->save();
            };

            for ($e = 0; $e < 4; $e++) {
                $rAdd = rand(7, 10);
                $hours = [];

                for ($r = 0; $r < 8; $r++) {
                    $time = $r + $rAdd;

                    if ($time < 10) {
                        $time = '0' . $time;
                    }

                    $hours[] = $time . ':00';
                };

                do {
                    $publicId = $this->generateUuid();
                } while (BarberAvailability::where('public_id', $publicId)->count() !== 0);

                $newBarberAvailability = new BarberAvailability();
                $newBarberAvailability->public_id = $publicId;
                $newBarberAvailability->id_barber = $newBarber->public_id;
                $newBarberAvailability->weekday = $e;
                $newBarberAvailability->hours = implode(',', $hours);
                $newBarberAvailability->save();
            };
        };

        return $array;
    }

    public function list(Request $request)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'lat',
            'lng',
            'city',
            'distance',
            'offset'
        ]);

        $validator = Validator::make($data, [
            'lat' => ['regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', 'required_with:lng'],
            'lng' => ['regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/', 'required_with:lat'],
            'distance' => ['integer', 'max:10', 'min:1']
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            if (isset($data['city'])) {
                $city = $data['city'];

                $response = $this->searchGeo($city);

                if ($response['results']) {
                    $lat = $response['results'][0]['geometry']['location']['lat'];
                    $lng = $response['results'][0]['geometry']['location']['lng'];
                };
            } else if (isset($data['lat'])) {
                $lat = $data['lat'];
                $lng = $data['lng'];

                $response = $this->searchGeo($lat . ',' . $lng);

                if (count($response['results']) > 0) {
                    $city = $response['results'][0]['formatted_address'];
                };
            } else {
                $lat = '-11.170840';
                $lng = '-61.906101';
                $city = 'Presidente Médici';
            };

            $distance = $data['distance'] ?? '10';
            $offset = $data['offset'] ?? '0';

            $barbers = Barber::select(Barber::raw('
                *, SQRT(POW(69.1 * (latitude - ' . $lat . '), 2) +
                POW(69.1 * (' . $lng . ' - longitude) * COS(latitude / 57.3), 2)) AS distance
            '))->havingRaw('distance <= ?', [$distance])
                ->orderBy('distance', 'ASC')->offset($offset)->limit(10)->get();

            foreach ($barbers as $key => $value) {
                $barbers[$key]['avatar'] = url('/media/barber-avatars/' . $barbers[$key]['avatar']);
            };

            if ($barbers) {
                $array['data'] = $barbers;
            } else {
                $array['error'] = 'This service is currently unavailable in your region';
            };

            $array['location'] = $city;
        };

        return $array;
    }

    public function one(string $id)
    {
        $array = [
            'error' => ''
        ];

        $validator = Validator::make(['public_id' => $id], [
            'public_id' => 'string|uuid|exists:barbers'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $barber = Barber::where('public_id', $id)->first();
            $barber->avatar = url('/media/barber-avatars/' . $barber->avatar);
            $barber->favorited = false;
            $barber->photos = [];
            $barber->services = [];
            $barber->testimonials = [];
            $barber->available = [];

            $isFavorited = UserFavorite::where('id_user', $this->loggedUser->public_id)
                ->where('id_barber', $barber->public_id)->first();

            if ($isFavorited) {
                $barber['favorited'] = true;
            };

            $barber['photos'] = BarberPhoto::select('url')
                ->where('id_barber', $barber->public_id)->get();

            foreach ($barber['photos'] as $photokey => $photo) {
                $barber['photos'][$photokey]['url'] = url('/media/uploads/' . $photo['url']);
            };

            $barber['services'] = BarberService::select([
                'public_id',
                'name',
                'desc',
                'photo',
                'price'
            ])->where('id_barber', $barber->public_id)->get();

            $barber['testimonials'] = BarberTestimonial::select([
                'public_id',
                'id_user',
                'name',
                'rate',
                'body'
            ])->where('id_barber', $barber->public_id)->get();

            $availability = [];

            $avails = BarberAvailability::where('id_barber', $barber->public_id)
                ->get();

            $availWeekdays = [];

            foreach ($avails as $item) {
                $availWeekdays[$item['weekday']] = explode(',', $item['hours']);
            };

            $appointments = [];

            $days = 92;
            $appQuery = UserAppointment::where('id_barber', $barber->public_id)
                ->whereBetween('ap_datetime', [
                    date('Y-m-d', strtotime("-12 hours")) . ' ' . '00:00:00',
                    date('Y-m-d', strtotime("+$days days")) . ' ' . '23:59:59'
                ])->get();

            foreach ($appQuery as $appItem) {
                $appointments[] = $appItem['ap_datetime'];
            };

            for ($q = -1; $q < $days; $q++) {
                $timeItem = strtotime("+$q days");
                $weekday = date('w', $timeItem);

                if (in_array($weekday, array_keys($availWeekdays))) {
                    $hours = [];

                    $dayItem = date('Y-m-d', $timeItem);

                    foreach ($availWeekdays[$weekday] as $hourItem) {
                        $dayFormatted = $dayItem . ' ' . $hourItem . ':00';

                        if (in_array($dayFormatted, $appointments) === false) {
                            $hours[] = $hourItem;
                        };
                    };

                    if (count($hours) > 0) {
                        $availability[] = [
                            'date' => $dayItem,
                            'hours' => $hours
                        ];
                    };
                };
            };

            $barber['available'] = $availability;

            $array['data'] = $barber;
        };

        return $array;
    }

    public function setAppointment(Request $request, string $id)
    {
        $array = [
            'error' => ''
        ];

        $data = $request->only([
            'service',
            'year',
            'month',
            'day',
            'hour',
            'minutes',
            'now'
        ]);

        $data['public_id'] = $id;

        $year = $data['year'] ?? '';
        $month = $data['month'] ?? '';
        $day = $data['day'] ?? '';
        $hour = $data['hour'] ?? '';
        $minutes = $data['minutes'] ?? '';

        $data['ap_datetime'] = '';
        $data['ap_weekday'] = '';
        $data['ap_hour'] = '';

        if ($year && $month && $day && ($hour || $hour === '0') && ($minutes || $minutes === '0')) {
            if ($month < 10) {
                $month = '0' . $month;
            };

            if ($day < 10) {
                $day = '0' . $day;
            };

            if ($hour < 10) {
                $hour = '0' . $hour;
            };

            if ($minutes < 10) {
                $minutes = '0' . $minutes;
            };

            $date = "$year-$month-$day $hour:$minutes:00";

            $data['ap_datetime'] = $date;
            $data['ap_weekday'] = gmdate('w', strtotime($date));
            $data['ap_hour'] = "$hour:$minutes";
        };

        $barberServices = BarberService::where('id_barber', $id)->pluck('public_id');
        $availWeekdays = BarberAvailability::where('id_barber', $id)->pluck('weekday');

        $availHours = [];

        $apWeekday = $data['ap_weekday'] ?? '';

        if ($apWeekday || $apWeekday === '0') {
            $availHours = BarberAvailability::where('id_barber', $id)
                ->where('weekday', $apWeekday)->first();

            if ($availHours) {
                $availHours = explode(',', $availHours['hours']);
            };
        };

        $validator = Validator::make($data, [
            'public_id' => 'string|uuid|exists:barbers',
            'service' => [
                'required',
                'string',
                'uuid',
                'exists:barber_services,public_id',
                Rule::in($barberServices)
            ],
            'year' => 'required|integer|min:1',
            'month' => 'required|integer|min:1|max:12',
            'day' => 'required|integer|min:1|max:31',
            'hour' => 'required|integer|max:23',
            'minutes' => 'required|integer|max:59',
            'ap_weekday' => [
                'required',
                Rule::in($availWeekdays)
            ],
            'ap_hour' => [
                'required',
                Rule::in($availHours)
            ],
            'now' => 'required|date',
            'ap_datetime' => 'required|date|unique:user_appointments'
        ]);

        if ($validator->fails()) {
            $array['error'] = $validator->errors();
        } else {
            $sentNow = new DateTime(date('Y-m-d H:i:s', strtotime($data['now'])));
            $minDate = new DateTime(gmdate('Y-m-d H:i:s', strtotime('-12 hours')));
            $maxDate = new DateTime(gmdate('Y-m-d H:i:s', strtotime('+14 hours')));

            $apDatetime = $data['ap_datetime'];

            if ($sentNow < $minDate || $sentNow > $maxDate) {
                $array['error'] = 'You may not get an appointment from future (or past)';
            } else {
                $service = $data['service'];

                do {
                    $publicId = $this->generateUuid();
                } while (UserAppointment::where('public_id', $publicId)->count() !== 0);

                $newAppointment = new UserAppointment();
                $newAppointment->public_id = $publicId;
                $newAppointment->id_user = $this->loggedUser['public_id'];
                $newAppointment->id_barber = $id;
                $newAppointment->id_service = $service;
                $newAppointment->ap_datetime = $apDatetime;
                $newAppointment->save();

                $newAppointment->confirmed = false;

                $array['data'] = $newAppointment;
            };
        };

        return $array;
    }

    private function searchGeo($address)
    {
        $address = urlencode($address);

        $key = env('MAPS_KEY', null);

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address .
            '&key=' . $key;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
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
