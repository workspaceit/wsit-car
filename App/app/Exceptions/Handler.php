<?php

namespace App\Exceptions;

use App\Libraries\Support\Jira;
use App\Mail\ExceptionOccurred;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use League\OAuth2\Server\Exception\OAuthServerException;
use SimpleUserAgent\UserAgent;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        OAuthServerException::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        /*** send exception to mail ***/
        $code = property_exists($exception, 'status') ? $exception->status :
            (property_exists($exception, 'statusCode') ? $exception->getStatusCode() : $exception->getCode());

        $ignoreExceptionMessages = [
            '',
            'Unauthenticated.',
            'The given data was invalid.'
        ];
        $ignoreExceptionCodes = [
            Response::HTTP_UNAUTHORIZED,
            Response::HTTP_FORBIDDEN,
            Response::HTTP_NOT_FOUND
        ];

        if (
            config('app.production')
            && request()->route() // Check it's not cron
            && !in_array($code, $ignoreExceptionCodes, true)
            && !in_array($exception->getMessage(), $ignoreExceptionMessages, true)
        ) {
            try {
                $userId = null;
                $guard = getUserGuard();
                if (!empty($guard)) {
                    $userId = Auth::guard($guard)->check() ? Auth::guard($guard)->user()->getAuthIdentifier() : null;
                }else{
                    $userId = Auth::check() ? Auth::user()->getAuthIdentifier() : null;
                }

                $userAgent = null;
                try {
                    $userAgent = (new UserAgent())->getInfo();
                }
                catch(Exception $e) {
                    Log::info(request()->header('User-Agent'));
                    Log::error($e);
                }

                if (!($exception instanceof ModelNotFoundException) && !($exception instanceof OAuthServerException)) {
                    Mail::send(new ExceptionOccurred(
                        $exception->getMessage(),
                        $exception->getTraceAsString(),
                        $userId,
                        now(),
                        request()->ip(),
                        $userAgent,
                        request()->fullUrl(),
                        request()->getMethod(),
                        url()->previous()
                    ));

                    # Create issue for exception
                    (new Jira)->createAppExceptionIssue($exception);
                }
            }
            catch(Exception $e) {
                Log::error($e);
            }
        }
        /*** send exception to mail END ***/

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
