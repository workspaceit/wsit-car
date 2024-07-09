<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
     * @return string
     */
    public function redirectTo()
    {
        return route('home');
    }

    /**
     * @param Request $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'exists:users,' . $this->username(),
            'password' => 'required|string',
            'g-recaptcha-response' => 'required|captcha'
        ]);
    }

    /**
     * @param Request $request
     * @param         $user
     * @return RedirectResponse
     * @throws ValidationException
     */
    protected function authenticated(Request $request, $user)
    {
        if (!$user->isSuperAdmin()) {
            $this->sendUserTypeErrorResponse();
        }

        if ($user->isBlocked()) {
            $this->sendBlockedResponse();
        }

        try {
            $defaultLang = '';
            $lang =  explode(',', $request->server('HTTP_ACCEPT_LANGUAGE'))[0];

            if (in_array($lang,  ['en', 'en-US', 'en-UK'])) {
                $defaultLang = 'en';
            } else if (in_array($lang,  ['fr', 'fr-FR', 'fr-CH'])) {
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
     * @param Request $request
     * @return \Illuminate\Foundation\Application|RedirectResponse|\Illuminate\Routing\Redirector|mixed
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        //Cookie::queue(Cookie::forget('language'));
        $confirm = $request->session()->has('auth.password_confirmed_at');
        $request->session()->invalidate();

        if ($confirm){
            $request->session()->put('auth.password_confirmed_at', time());
        }

        return $this->loggedOut($request) ?: redirect('/');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.admin.login');
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

    /**
     * The user has logged out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        return redirect(route("admin.login"));
    }
}
