<?php

namespace App\Exceptions;

use Exception;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\Console\Exception\RuntimeException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the exception types that should not be reported if they contain certain messages.
     *
     * @var array
     */
    protected $dontReportMessages = [
        RuntimeException::class => [
            'Not enough arguments',
        ],
        ServerException::class => [
            'Server error:',
        ],
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        if (config('app.production')) {
            foreach ($this->dontReportMessages as $type => $messages) {
                if ($exception instanceof $type && str_contains($exception->getMessage(), $messages)) {
                    return;
                }
            }
        }

        parent::report($exception);
    }
}
