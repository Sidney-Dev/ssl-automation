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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Exceptions\DomainAlreadyExists;
use App\Exceptions\InvalidDomainException;


class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $allCertificateInfos = LetsEncryptCertificate::with('domains')->get();
    
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
        $request->validate([
            'domain' => 'required'
        ]);


        // TODO: validate the domain
        $domain = $request->domain;

        $this->validateDomain($domain);
        $this->checkDomainDoesNotExist($domain);

        $email = Auth::user()->email;
        exec(env('ROOT_DIR') . "register ".$email);

        // generate the certificate and ensure to only save records in the database once the certificate is generated
        $certificate = new LetsEncryptCertificate;

        $certificate->generate($domain);
  
        if($certificate->error == false ) {      
            $certificate->domain = $domain;
            $certificate->certificate_validation_date = $certificate->certificate_validation_date;
            $certificate->fullchain_path = env('MAIN_DIR').'/.acmephp/master/certs/'. $domain . '/public/fullchain.pem';
            $certificate->chain_path = env('MAIN_DIR').'/.acmephp/master/certs/'. $domain . '/public/chain.pem';
            $certificate->cert_path = env('MAIN_DIR').'/.acmephp/master/certs/'. $domain .'/public/cert.pem';
            $certificate->privkey_path = env('MAIN_DIR').'/.acmephp/master/certs/'. $domain . '/private/key.private.pem';

            $certificate->save();
            return redirect('/certificates')->with('success', 'Certificate has been generated for ' . $domain);
        } else {
            return redirect('/certificates')->with('error', 'Failed to generate the certificate for ' . $domain);
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
        $environmentDetails = $env->getEnvironments();
        // $environmentDetails = "test";

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
        $getSlugAndEnvId = LetsEncryptCertificate::where('id',$id)->first();
       
        $env = new Environments();
        $response = $env->certificateDeletion($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);
        
        if ($response == true) {
            // LetsEncryptCertificate::where('id',$id)->delete(); // it hides from showing but the record is still in the DB
            // $this->removeFiles($getSlugAndEnvId->domain); // remove directories
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

    public function removeFiles($domain) {
        $path = env('MAIN_DIR').'/.acmephp/master/certs/'. $domain;

        if (File::exists($path)) {
            File::deleteDirectory($path);
        }
    }

    /**
     * Checks mainly to prevent API errors when a user passes e.g. 'https://domain.com' as a domain. This should be
     * 'domain.com' instead.
     * @param string $domain
     * @throws InvalidDomainException
     */
    public function validateDomain(string $domain): void
    {
        if (Str::contains($domain, [':', '/', ','])) {
            throw new InvalidDomainException($domain);
        }
    }

    /**
     * @param string $domain
     * @throws DomainAlreadyExists
     */
    public function checkDomainDoesNotExist(string $domain): void
    {
        if (LetsEncryptCertificate::withTrashed()->where('domain', $domain)->exists()) {
            throw new DomainAlreadyExists($domain);
        }
    }
}
