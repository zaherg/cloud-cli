<?php

if (! function_exists('env_path')) {
    function env_path()
    {
        return app()->make('path.env');
    }
}

if (! function_exists('getEnvPath')) {
    function getEnvPath()
    {
        return getenv('HOME') . DIRECTORY_SEPARATOR . '.cloudflare';
    }
}

if (! function_exists('ClientException')) {
    function ClientException($exception)
    {
        $errors = collect(json_decode((string) $exception->getResponse()->getBody())->errors);

        $messages = $errors->map(function ($error) {
            return $error->message;
        });

        return $messages;
    }
}
