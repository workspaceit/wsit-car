<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\CarsController;
use App\Http\Controllers\Controller;
use App\Libraries\Support\Captcha;
use App\Models\Car;
use App\Models\Dealer;
use App\Models\Language;
use App\Models\Status;
use App\Models\Type;
use App\Models\User;
use App\Models\VehicleMake;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Laravel\Socialite\Facades\Socialite;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

      /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $captcha = new Captcha(
            config('captcha.secret'),
            config('captcha.sitekey'),
            config('captcha.options')
        );

        $id = rand(99999, 9999999);
        $options = CarsController::options();
        $car_body = CarsController::car_body();
        $makes = VehicleMake::selectRaw('id, name, UPPER(name) AS upper_name')
            ->orderBy('name')->pluck('name', 'upper_name');

        Cache::forget("car_data");
        return view('auth.create', compact('captcha', 'id', 'options', 'car_body', 'makes'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        Cache::forget("car_data");
       return Validator::make($data, [
            'car_exterior_color'   => 'required',
            'car_body'             => 'required',
            'stock'                => 'nullable',
            'car_vin'              => 'required',
            'car_transmission'     => 'required',
            'car_drivetrain'       => 'required',
            'car_cylinders'        => 'required',
            'car_engine_size'      => 'required',
            'car_interrior_color'  => 'required',
            'number_of_passengers' => 'required',
            'car_fuel_type'        => 'required',
            'model'                => 'required',
            'maker'                => 'required',
            'description'          => 'required',
            'car_condition'        => 'required',
            'car_certified'        => 'nullable',
            'title'                => 'required',
            'notes'                => 'nullable',
            'car_featured'         => 'nullable',
            'car_no_accident'      => 'nullable',
            'car_one_owner'        => 'nullable',
            'car_starter'          => 'nullable',
            'photos'               => 'nullable',
            'car_trim'             => 'nullable|string',
            'car_year'             => 'required|digits:4|integer',
            'car_price'            => 'required|numeric|max: 2147483647',
            'car_old_price'        => 'nullable|numeric|max: 2147483647',
            'car_mileage'          => 'required|numeric|min:0|max:1000000',
            'social_login'         => 'nullable|in:facebook,google',
            'name'                 => [empty($data['social_login']) ? 'required' : 'nullable', 'max:255'],
            'phone'                => [empty($data['social_login']) ? 'required' : 'nullable', 'max:255'],
            'password'             => [empty($data['social_login']) ? 'required' : 'nullable', 'string', 'min:6'],
            'email'                => [empty($data['social_login']) ? 'required' : 'nullable', 'email', 'max:255', 'unique:users,email'],
        ],[
            'email.unique' => __('user.form.validations.email.unique')
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        event(new Registered($user = $this->create($request->all())));

        if(empty($request->input('social_login'))){
            $this->guard()->login($user);
            return $this->registered($request, $user) ?: redirect($this->redirectPath());
        }else{
            return Socialite::driver($request->input('social_login'))->redirect();
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $formData)
    {
        $dealer = Dealer::where('dealer_email', Dealer::INDIVIDUAL_DEALER_MAIL)->firstOrfail();
        $data = array_except($formData, ['name', 'email', 'vehicle_type', 'phone', 'password', 'social_login', 'title', '_token', 'photos', 'car_options', 'description', 'notes']);
        $data['post_title'] = $formData['title'];
        $data['type'] = 'manual';
        $data['ID'] = $this->getCarIdAttribute(request());
        $this->setCarAttributes(request(), $data);
        $dealer->setCarDealerAttributes(request(), $data);

        if(empty($formData['social_login'])){
            $user = User::create([
                'username'          => $formData['email'],
                'email'             => $formData['email'],
                'password'          => Hash::make($formData['password']),
                'active'            => 1,
                'type'              => 'individual',
                'language_id'       => Language::where('name', app()->getLocale())->first()->id ?? 1,
                'is_approved'       => 0,
                'level'             => 3,
                'email_verified_at' => null,
                'dealers'           => [(string) $dealer->id ?? null]
            ]);

            $data['user_id'] = $user->id;
            $user->assignRole($user->type);
            Car::forceCreate($data);

            $user->profile()->firstOrCreate([
                    'user_id' => $user->id
                ],[
                    'email'      => $formData['email'],
                    'phone'      => $formData['phone'] ?? "",
                    'first_name' => $formData['name'] ?? "",
                ]);

            return $user;
        }else{
            Cache::put("car_data", $data, Carbon::now()->addYear());

            return null;
        }
    }

    /**
     * @param ActionRequest $request
     * @param               $data
     * @param Car|null      $car
     *
     * @return void
     */
    public function setCarAttributes(Request $request, &$data): void
    {
        if (!empty($request->photos)) {
            $data['photo'] = json_decode($request->photos)[0] ?? null;
        }

        $data = array_merge($data, [
            'status'                => 29,
            'photos'                => implode(',', json_decode($request->photos)),
            'admin_note'            => $request->input('notes'),
            'stock'                 => $request->input('stock', ''),
            'car_body'              => $request->input('car_body', ''),
            'car_mileage'           => $request->input('car_mileage', ''),
            'car_cylinders'         => $request->input('car_cylinders', '4'),
            'car_drivetrain'        => $request->input('car_drivetrain', ''),
            'car_doors_count'       => $request->input('car_doors_count', '4'),
            'car_mileage_unit'      => $request->input('car_mileage_unit', ''),
            'car_transmission'      => $request->input('car_transmission', ''),
            'car_engine_size'       => $request->input('car_engine_size', '2.5'),
            'car_exterior_color'    => $request->input('car_exterior_color', ''),
            'number_of_passengers'  => $request->input('number_of_passengers', 5),
            'car_fuel_type'         => $request->input('car_fuel_type', 'GASOLINE'),
            'car_interrior_color'   => $request->input('car_interrior_color', 'BLACK'),
            'car_trim'              => !empty($request->car_trim) ? $request->input('car_trim') : '',
            'car_sub_model'         => !empty($request->car_trim) ? $request->input('car_trim') : '',
            'car_certified'         => filter_var($request->car_certified, FILTER_VALIDATE_BOOLEAN),
            'car_featured'          => filter_var($request->car_featured, FILTER_VALIDATE_BOOLEAN),
            'car_no_accident'       => filter_var($request->car_no_accident, FILTER_VALIDATE_BOOLEAN),
            'car_starter'           => filter_var($request->car_starter, FILTER_VALIDATE_BOOLEAN),
            'car_one_owner'         => filter_var($request->car_one_owner, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_fb'         => filter_var($request->fb_check_input, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_msn'        => filter_var($request->msn_check_input, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_shop'       => filter_var($request->shop_check_input, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_kijiji'     => filter_var($request->kijiji_check_input, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_heyauto'    => filter_var($request->heyauto_check_input, FILTER_VALIDATE_BOOLEAN),
            'push_to_website'       => filter_var($request->website_check_input, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_cargurus'   => filter_var($request->cargurus_check_input, FILTER_VALIDATE_BOOLEAN),
            'add_to_csv_autotrader' => filter_var($request->autotrader_check_input, FILTER_VALIDATE_BOOLEAN),
            'syndication_export'    => filter_var($request->syndication_export_input, FILTER_VALIDATE_BOOLEAN),
            'car_options'           => implode(', ', !empty($request->car_options) ? json_decode($request->car_options) : ['Tuner/radio']),
            'vehicle_type_id'       => Type::firstOrCreate(['name' => strtolower(trim($request->vehicle_type))])->id,
            'approval_status'       => Status::firstOrCreate(['name' => 'pending'])->id
        ]);
    }

    /**
     * @param $request
     *
     * @return int|mixed
     */
    protected function getCarIdAttribute($request)
    {
        $chkID = $request->input('ID', rand(99999, 9999999));
        do {
            $isExists = Car::where('dealer_id', $request->dealer_id)->where('ID', $chkID)->exists();
            if ($isExists) {
                $chkID = rand(99999, 9999999);
            }
        } while ($isExists);

        return $chkID;
    }

    public function updateImages(Request $request)
    {
        $out = [];
        $photos = [];
        $request->validate(['photos.*' => 'nullable|mimes:jpg,jpeg,png,webp,heic,heif']);

        if ($request->photos) {
            if (array_has($request->all(), 'photos') && $request->file('photos')) {
                foreach ($request->file('photos') as $file) {
                    $extension  = $file->getClientOriginalExtension();
                    $path       = $file->store('', 'car-images');
                    if ($extension == 'heic' || $extension == 'HEIC') { //if heic convert it to jpg

                        $path = convertHeicToJpg('cars', $path);
                    }

                    $new_file_name = saveImageInWebp('cars', $path);
                    $photo_url = config('app.production') ?  env('AWS_URL') . '/' . $new_file_name :
                        env('AWS_URL') . '/images/cars/' . $new_file_name ;;

                    $photos[] = $photo_url;
                    $config[] = [
                        'key' => $photo_url,
                        'downloadUrl' => $photo_url, // the url to download the file
                        'url' => '/individuals/cars/images/destroy', // server api to delete the file based on key
                    ];
                }
            }
            $out = ['initialPreview' => end($photos), 'initialPreviewConfig' => $config, 'initialPreviewAsData' => true];
        } else {
            $photoURL = str_replace(str_split('\[]"'), "", $request->initialPreview);
            $photosp = explode(',', $photoURL);
            foreach ($photosp as $file) {
                $config[] = [
                    'key' => $file,
                    'downloadUrl' => $file, // the url to download the file
                    'url' => '/cars/deleteImage', // server api to delete the file based on key
                ];
            }

            $out = ['initialPreviewConfig' => $config, 'initialPreviewAsData' => true];
        }

        return response()->json($out);
    }

    public function destroyImage(Request $request)
    {
        $response = response()->json('Success');
        DB::transaction(function() use($request, &$response){
            $response = (new CarsController())->deleteImageFile(last(explode('/', $request->key)));
        });

        return $response;
    }
}
