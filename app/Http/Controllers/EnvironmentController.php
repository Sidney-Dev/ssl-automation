<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Environments;

class EnvironmentController extends Controller
{
    public function addCertificateToEnvironment(Request $request) {
        $validateData = $request->validate([
            'environment' => 'required',
            'cert_name' => 'required',
        ]);
        
        $env = new Environments();
        $response = $env->addCertificateToEnvironment($request->cert_name, $request->environment, $request->domain);
       
        if ($response == true) {
            return redirect('/certificates')->with('success', "Certificate has been installed");
        }
    }

    public function index() 
    {
        $env = new Environments();
        $environmentDetails = $env->getEnvironments();

        return view('environments', compact('environmentDetails'));
    }
}
