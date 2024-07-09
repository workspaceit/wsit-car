<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Dealer;
use App\Models\Language;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $maxAttempts = 3; // Default is 5
    protected $decayMinutes = 5; // Default is 1

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required|captcha'
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $login = $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
        if(!$login && filter_var($request->username, FILTER_VALIDATE_EMAIL)){
            $login = $this->guard()->attempt(
                ['email' => $request->username, 'password'=> $request->password], $request->filled('remember')
            );
        }

        return $login;
    }

    /**
     * @param Request $request
     * @param         $user
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->isSuperAdmin()) {
            $this->sendUserTypeErrorResponse();
        }

        if ($user->isBlocked()) {
            $this->sendBlockedResponse();
        }

        try {
            $defaultLang = '';
            $lang = explode(',', $request->server('HTTP_ACCEPT_LANGUAGE'))[0];

            if (in_array($lang, ['en', 'en-US', 'en-UK'])) {
                $defaultLang = 'en';
            } else if (in_array($lang, ['fr', 'fr-FR', 'fr-CH'])) {
                $defaultLang = 'fr';
            }

            app()->setLocale($defaultLang ?? 'en');
            $request->session()->put('setLang', $defaultLang);
        }catch (\Throwable $e) {}

        $user->update(['last_login' => Carbon::now()->toDateTimeString(),]);
        $request->session()->flash('collapsedSidebar', true);

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Return authenticate user landing page url
     *
     * @return string
     */
    public function redirectTo(): string
    {
        if (auth()->user()->isBuyer()) {
            return route('advance-search.manage');
        }
        if (auth()->user()->isTransporter()) {
            return route('transporters.requests.manage');
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }

    public function logout(Request $request)
    {
        // Logout from new theme
        $url = env('NEW_THEME_URL', 'https://drivegood.com').'/user-logout?user_id='.auth()->user()->id;

        $client = new \GuzzleHttp\Client(['timeout' => 1]);
        $promise = $client->requestAsync('GET', $url);

        try {
            $promise->wait();
        } catch (\Exception $ex) {
            ## Handle
        }

        $this->guard()->logout();
        //Cookie::queue(Cookie::forget('language'));
        $confirm = $request->session()->has('auth.password_confirmed_at');
        $request->session()->invalidate();

        if ($confirm) {
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return $this->loggedOut($request) ?: redirect('/');
    }

    /**
     * @throws ValidationException
     */
    private function sendBlockedResponse()
    {
        $this->guard()->logout();
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.blocked')],
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function sendUserTypeErrorResponse()
    {
        $this->guard()->logout();
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    public function redirectToSocial($provider)
    {
        abort(403);
        return Socialite::driver($provider)->redirect();
    }

    public function handleSocialCallback($provider)
    {
        try{
            $data = Cache::get('car_data');
            $userSocial = Socialite::driver($provider)->user();
            Log::info(sprintf("%s social logged in user info : %s", ucfirst($provider), json_encode($userSocial)));
            $user = $user = User::where('email', $userSocial->getEmail())->orWhere('social_id',  $userSocial->getId())->first();

            if(empty($user) && $userSocial){
                $user = User::firstOrCreate([
                    'social_id' => $userSocial->getId()
                ],[
                    'username'          => $userSocial->getName(),
                    'email'             => $userSocial->getEmail(),
                    'password'          => Hash::make(Str::random(6)),
                    'active'            => 1,
                    'type'              => 'individual',
                    'language_id'       => Language::where('name', app()->getLocale())->first()->id ?? 1,
                    'is_approved'       => 0,
                    'level'             => 3,
                    'photo'             => $userSocial->getAvatar(),
                    'email_verified_at' => !empty($userSocial->getEmail()) ? Carbon::now() : null,
                    'dealers'           => [(string) Dealer::where('dealer_email', Dealer::INDIVIDUAL_DEALER_MAIL)->first()->id ?? null]
                ]);

                /*$user->profile()->firstOrCreate([
                    'user_id' => $user->id
                ],[
                    'email' =>  $userSocial->getEmail(),
                    'logo_url' => $userSocial->getAvatar(),
                    'first_name'=> $userSocial->user['given_name'] ?? "",
                    'last_name' => $userSocial->user['family_name'] ?? "",
                ]);*/
            }

            if ($user) {
                $user->syncRoles($user->type);
                $data['user_id'] = $user->id;
                Car::forceCreate($data);

                Auth::login($user);
                return redirect()->intended($this->redirectPath());
            }
        }catch(Throwable $th){
            Log::error("Error occurs in handleSocialCallback : ". $th->getMessage());
        }

        return redirect()->route('login')->withErrors((new MessageBag())->add('username', sprintf('Failed to login usign %s account.', $provider)));
    }
}
