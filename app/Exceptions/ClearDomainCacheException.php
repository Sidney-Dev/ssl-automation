<?php

namespace App\Exceptions;

use Exception;

class ClearDomainCacheException extends Exception
{
    function render() {
        return redirect('/certificates')->with('error', "Domain cache has not been cleared");
    }
}
