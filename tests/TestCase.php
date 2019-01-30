<?php

namespace Tests;

use LaravelZero\Framework\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getFixtures($fileName): \stdclass
    {
        return \GuzzleHttp\json_decode(
            File::get(base_path(sprintf('tests/Fixtures/Endpoints/%s.json', $fileName)))
        );
    }
}
