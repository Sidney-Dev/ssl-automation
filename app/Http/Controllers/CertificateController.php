<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AcmePhp\Core\Http\Base64SafeEncoder;
use AcmePhp\Core\Http\SecureHttpClientFactory;
use AcmePhp\Core\Http\ServerErrorHandler;
use AcmePhp\Ssl\Parser\KeyParser;
use AcmePhp\Ssl\Signer\DataSigner;
use App\LetsEncrypt;
use GuzzleHttp\Client as GuzzleHttpClient;
use App\Models\LetsEncryptCertificate;
use App\Models\Domains;
use App\Environments;
use Illuminate\Support\Str;


class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allCertificateInfos = LetsEncryptCertificate::get();
        return view('certificates', compact("allCertificateInfos"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("create-certificate");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $domain = $request->domain;

        $validateData = $request->validate([
            'domain' => 'required'
        ]);

        $register = exec("php /Users/sidneydesousa/acmephp.phar register esp.sousa@gmail.com");

        // TODO: validate the domain
        $domain = $request->domain;

        try {
            // generate the certificate and ensure to only save records in the database once the certificate is generated
            $certificate = new LetsEncryptCertificate;

            $message = $certificate->generate($domain);
            
            if(!empty($generate)) {      
                $certificate->domain = $domain;
                $certificate->save();
            }
            return redirect('/certificates')->with('success', $message);

        } catch(\Exception $e) {

            dd("Something went wrong");
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(LetsEncryptCertificate $certificate)
    {
        $env = new Environments();
        // $environmentDetails = $env->getEnvironments();
        $environmentDetails = "test";

        $domains =$certificate->with('domains')->first();
        
        return view('certificate-details', compact('certificate','environmentDetails', 'domains'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // if (LetsEncryptCertificate::where('id',$id)->delete()) {
        //     return redirect('/certificates')->with('success', 'Domain deleted');
        // } else {
        //     return redirect('/certificates')->with('error', 'Domain could not be deleted');
        // }
       
        $getSlugAndEnvId = LetsEncryptCertificate::where('id',$id)->first();
        $env = new Environments();
        $response = $env->certificateDeletion($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);
        
        if ($response == true) {
            // LetsEncryptCertificate::where('id',$id)->delete();
            return redirect('/certificates')->with('success', 'Certificate has been deleted');
        }
    }

    public function activate($id) {
        $getSlugAndEnvId = LetsEncryptCertificate::select('slug', 'environmentID')->where('id',$id)->first(); 
        
        if($getSlugAndEnvId->slug) {
            $env = new Environments();
            $response = $env->certificateActivation($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);
    
            if ($response == true) {
                LetsEncryptCertificate::where('id',$id)->update(['status' => 'success']);
                return redirect('/certificates')->with('success', "Certificate has been activated");
            }
        } else {
            return redirect('/certificates')->with('error', "Please ensure the certificate is installed first");
        }
    }

    public function deactivate($id) {
        $getSlugAndEnvId = LetsEncryptCertificate::select('slug', 'environmentID')->where('id',$id)->first(); 
        
        if($getSlugAndEnvId->slug) {
            $env = new Environments();
            $response = $env->certificateDeactivation($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);
    
            if ($response == true) {
                LetsEncryptCertificate::where('id',$id)->update(['status' => 'pending']);
                return redirect('/certificates')->with('success', "Certificate has been deactivated");
            }
        } 
    }

    public function storeDomains(Request $request, LetsEncryptCertificate $certificate) {

        $domains = $certificate->generate($certificate->domain, $request->domains);

        // dd($certificate->id);
        if(is_array($domains)) {
            foreach($domains as $domain) {
                $generate = Domains::create([
                    'name' => trim($domain),
                    'lets_encrypt_certificate_id' => $certificate->id
                ]);
            }
        }
        
        return redirect()->route("certificate-details", $certificate->id);

    }
}
