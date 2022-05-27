<?php

namespace App\Exceptions;

use Exception;

class CertificateNotDeactivatedException extends Exception
{
    function render() {
        return redirect('/certificates')->with('error', "Certificate has not been deactivated");
    }
}
