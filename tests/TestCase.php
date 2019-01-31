<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getFixtures($fileName): \stdclass
    {
        return \GuzzleHttp\json_decode(
            File::get(base_path(sprintf('tests/Fixtures/Endpoints/%s.json', $fileName)))
        );
    }

    /**
     * Specify output that should be printed when the command runs.
     *
     * @param string $output
     *
     * @return $this
     */
    public function expectsOutputContains($output)
    {
        $this->test->expectedOutput[] = $output;

        return $this;
    }
}
