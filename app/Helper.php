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
