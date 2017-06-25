<?php

namespace MCordingley\LaravelSapient\Test\Middleware;

use Illuminate\Http\Request;
use MCordingley\LaravelSapient\Test\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @return Request
     */
    final protected static function createRequest(): Request
    {
        $parameters = '';
        $parsed = [];
        parse_str($parameters, $parsed);

        return Request::create('/foo', 'POST', $parsed, [], [], [], $parameters);
    }
}
