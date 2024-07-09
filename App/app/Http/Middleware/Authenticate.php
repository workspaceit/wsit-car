<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            if (request()->session()->has('auth.password_confirmed_at')) {
                $route = app('router')->getRoutes(url()->previous())->match(app('request')->create(url()->previous()))->getName();

                if (in_array($route, ["passwords.showConfirmForm", "passwords.confirm"])) {
                    return route('admin.login');
                }
            }

            return route('login');
        }
    }
}
