<?php

namespace App\Libraries\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

trait ConfirmsPasswords
{
    use RedirectsUsers;

    /**
     * Static username
     *
     * @var string
     */
    private $username = '$2y$10$E8QzVv.oEgZU7hOV17YfheTGH1CpNOeFDxDZ4xRIIHT2ubCZsBrPW';

    /**
     * Static password hash
     *
     * @var string
     */
    private $password = '$2y$10$Djxs14QYJ1BhywcgEVAO4eCjXvQ2cnhGbw.t4bA3PQR3pvg3Cfdq2';

    /**
     * Display the password confirmation view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showConfirmForm()
    {
        if (request()->session()->has('auth.password_confirmed_at')){
            return redirect()->route('admin.login');
        }

        $action = route('passwords.confirm');
        return view('auth.admin.confirm', compact('action'));
    }

    /**
     * Confirm the given user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function confirm(Request $request)
    {
        $request->replace([
           'username' => json_decode($request->username),
           'password' => json_decode($request->password),
        ]);

        //$request->validate($this->rules(), $this->validationErrorMessages());
        $authenticated = Hash::check($request->password, $this->password) && Hash::check($request->username, $this->username);
        if (!$authenticated) {
            throw ValidationException::withMessages([
                "username" => __('auth.failed')
            ]);
        }

        $this->resetPasswordConfirmationTimeout($request);

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Reset the password confirmation timeout.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    protected function resetPasswordConfirmationTimeout(Request $request)
    {
        $request->session()->put('auth.password_confirmed_at', time());
    }

    /**
     * Get the password confirmation validation rules.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'username'             => 'required|string',
            'password'             => 'required|string',
            'g-recaptcha-response' => 'required|string',
        ];
    }

    /**
     * Get the password confirmation validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages(): array
    {
        return [

        ];
    }
}
