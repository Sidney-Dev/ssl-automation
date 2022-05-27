<?php

namespace App\Exceptions;

use Exception;

class CertificateNotDeletedException extends Exception
{
    function render() {
        return redirect('/certificates')->with('error', "Certificate has not been deleted");
    }
}
