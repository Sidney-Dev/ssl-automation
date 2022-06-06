<?php

namespace App\Exceptions;

use Exception;

class CertificateNotInstalledException extends Exception
{
    function render() {
        return redirect('/certificates')->with('error', "Certificate has not been installed. try the install link");
    }
}
