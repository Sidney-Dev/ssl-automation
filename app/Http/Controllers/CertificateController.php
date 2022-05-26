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
use App\Domains;
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
        $new = new LetsEncrypt(
            new SecureHttpClientFactory(
                new GuzzleHttpClient(),
                new Base64SafeEncoder(),
                new KeyParser(),
                new DataSigner(),
                new ServerErrorHandler()
            )
        );
        $validateData = $request->validate([
            'domain' => 'required'
        ]);

        $is_creating = 0;

        if (Str::contains($request->domain,",")) {
            $domains = explode(",",$request->domain);

            foreach($domains as $domain) {
                if ($new->createNow($domain)) {
                    $is_creating++;
                }
            }

            if ($is_creating !=0) {
                return redirect('/certificates')->with('success', 'Certificate has been generated for ' . implode(",", $domains));
            }
        } else {
            if ($new->createNow($request->domain)) {
                return redirect('/certificates')->with('success', 'Certificate has been generated for ' . $request->domain);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $certificateInfos = LetsEncryptCertificate::where('id', '=', $id)->first();
        $env = new Environments();
        $environmentDetails = $env->getEnvironments();
      
        return view('certificate-details', compact('certificateInfos','environmentDetails'));
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
            LetsEncryptCertificate::where('id',$id)->delete();
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
}
