<?php

namespace App\Exceptions;

use Exception;

class EnvironmentNotFoundException extends Exception
{
    function render() {
        return redirect('/certificates')->with('error', "Environment not found");
    }
}
