<?php

namespace Bogddan\Redirects\Exceptions;

use Exception;

class RedirectException extends Exception
{
    /**
     * The exception to be thrown when the old url is the same as the new url.
     */
    public static function sameUrls(): RedirectException
    {
        return new static('The old url cannot be the same as the new url!');
    }
}
