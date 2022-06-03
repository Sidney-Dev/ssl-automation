<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Environments;
use App\Models\LetsEncryptCertificate;

class EnvironmentController extends Controller
{
    public function index() 
    {
        $env = new Environments();
        $environmentDetails = $env->getEnvironments();

        return view('environments', compact('environmentDetails'));
    }
}
