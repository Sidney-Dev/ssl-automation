<?php

namespace App\Exceptions;

use Exception;

class CertificateNotActivatedException extends Exception
{
    function render() {
        return redirect('/certificates')->with('error', "Certificate has not been activated. Try clicking on the activate link again");
    }
}
