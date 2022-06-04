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
        exec(env('ROOT_DIR') . "register " . $email);

        // generate the certificate and ensure to only save records in the database once the certificate is generated
        $certificate = new LetsEncrypt;
        $certificate->generate($domain);

        if ($certificate->error == false) {
            LetsEncryptCertificate::create([
                'domain' =>  $domain,
                'certificate_validation_date' => $certificate->certificateValidationDate,
                'fullchain_path' =>  env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/public/fullchain.pem',
                'chain_path' => env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/public/chain.pem',
                'privkey_path' => env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/private/key.private.pem',
                'last_renewed_at' => now(),
                'created'=> true,
                'status'=> 'pending',
            ]);
            return redirect('/certificates')->with('success', 'Certificate has been generated for ' . $domain);
        } else {
            return redirect('/certificates')->with('error', $certificate->errorMessage);
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
        // $env = new Environments();
        // $environmentDetails = $env->getEnvironments();

        $domains = $certificate->with('domains')->first();

        return view('certificate-details', compact('certificate', 'domains'));
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
        $getSlugAndEnvId = LetsEncryptCertificate::where('id', $id)->first();

        $env = new Environments();
        $response = $env->certificateDeletion($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);

        if ($response == true) {
            LetsEncryptCertificate::where('id',$id)->delete(); // it hides from showing but the record is still in the DB
            // $this->removeFiles($getSlugAndEnvId->domain); // remove directories
            return redirect('/certificates')->with('success', 'Certificate has been deleted');
        }
    }

    public function activate($id)
    {
        $getSlugAndEnvId = LetsEncryptCertificate::select('slug', 'environmentID')->where('id', $id)->first();

        if ($getSlugAndEnvId->slug) {
            $env = new Environments();
            $response = $env->certificateActivation($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);

            if ($response == true) {
                LetsEncryptCertificate::where('id', $id)->update(['status' => 'activated']);

                if ($env->clearDomainCache($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug)) {
                    LetsEncryptCertificate::where('id', $id)->update(['status' => 'success']);
                    return redirect('/certificates')->with('success', "Certificate has been activated the cache has been cleared");
                }
            }
        } else {
            return redirect('/certificates')->with('error', "Please ensure the certificate is installed first");
        }
    }

    public function deactivate($id)
    {
        $getSlugAndEnvId = LetsEncryptCertificate::select('slug', 'environmentID')->where('id', $id)->first();

        if ($getSlugAndEnvId->slug) {
            $env = new Environments();
            $response = $env->certificateDeactivation($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);

            if ($response == true) {
                LetsEncryptCertificate::where('id', $id)->update(['status' => 'deactivated']);
                return redirect('/certificates')->with('success', "Certificate has been deactivated");
            }
        }
    }

    public function addCertificateToEnvironment(Request $request)
    {
        $validateData = $request->validate([
            'environment' => 'required',
            'cert_name' => 'required',
        ]);

        $env = new Environments();
        $response = $env->addCertificateToEnvironment($request->cert_name, $request->environment, $request->domain);

        if ($response == true) {
            LetsEncryptCertificate::where('domain', $request->domain)->update(['status' => 'installed']);
            return redirect('/certificates')->with('success', "Certificate has been installed");
        }
    }

    public function storeDomains(Request $request, LetsEncryptCertificate $certificate)
    {
        // Validate domain by checking invalid characters
        $this->validateDomain($request->domains);
        $aDomains = explode("\r\n", $request->domains);

        // Check if domain already exists
        foreach ($aDomains as $domain) {
            $this->checkDomainDoesNotExist($domain);
        }

        // Check parent domain status
        $env = new Environments();

        if ($certificate->status == "installed") {
            $env->certificateDeletion($certificate->environmentID, $certificate->slug); // delete cert from acquia environment
            LetsEncryptCertificate::where('id', $certificate->id)->update(['status' => 'pending', 'slug' => null, 'environmentID' => null]);

            $this->removeFiles($certificate->domain);
        }

        if ($certificate->status == "activated" || $certificate->status == "success") {

            $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
            LetsEncryptCertificate::where('id', $certificate->id)->update(['status' => 'deactivated']);

            $env->certificateDeletion($certificate->environmentID, $certificate->slug);
            LetsEncryptCertificate::where('id', $certificate->id)->update(['status' => 'pending', 'slug' => null, 'environmentID' => null]);

            $this->removeFiles($certificate->domain);
        }

        $generateCertificate = new LetsEncrypt;
        $domains = $generateCertificate->generate($certificate->domain, $request->domains);


        if ($generateCertificate->error == false) {
            if (is_array($domains)) {
                foreach ($domains as $domain) {
                    Domains::updateOrCreate([
                        'name' => trim($domain),
                        'lets_encrypt_certificate_id' => $certificate->id,
                    ]);
                }
            }
            $certificate->updated(['updated_at' => date('Y-m-d H:i:s'), 'certificate_validation_date' => $generateCertificate->certificateValidationDate]);
            return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains added to certificate');
        } elseif ($generateCertificate->errorMessage != null) {
            return redirect()->route("certificate-details", $certificate->id)->with('error', $generateCertificate->errorMessage);
        } else {
            return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be added to certificate');
        }
    }

    public function deleteDomains(Request $request, LetsEncryptCertificate $certificate)
    {
        $domains = null;

        foreach ($certificate->domains as $domain) {
            if ($domain->name != $request->subdomain) {
                $domains .= $domain->name . "\r\n";
            }
        }
        $newDomains = rtrim($domains, "\r\n");

        // Check parent domain status
        $env = new Environments();

        if ($certificate->status == "installed") {
            $env->certificateDeletion($certificate->environmentID, $certificate->slug); // delete cert from acquia environment
            LetsEncryptCertificate::where('id', $certificate->id)->update(['status' => 'pending', 'slug' => null, 'environmentID' => null]);

            $this->removeFiles($certificate->domain);
        }

        if ($certificate->status == "activated" || $certificate->status == "success") {

            $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
            LetsEncryptCertificate::where('id', $certificate->id)->update(['status' => 'deactivated']);

            $env->certificateDeletion($certificate->environmentID, $certificate->slug);
            LetsEncryptCertificate::where('id', $certificate->id)->update(['status' => 'pending', 'slug' => null, 'environmentID' => null]);
            $this->removeFiles($certificate->domain);
        }

        $generateCertificate = new LetsEncrypt;
        $domains = $generateCertificate->generate($certificate->domain, $newDomains);

        if ($generateCertificate->error == false) {
            if (is_array($domains)) {
                foreach ($domains as $domain) {
                    Domains::updateOrCreate([
                        'name' => trim($domain),
                        'lets_encrypt_certificate_id' => $certificate->id,
                    ]);
                }
            }
            $certificate->updated(['updated_at' => date('Y-m-d H:i:s'), 'certificate_validation_date' => $generateCertificate->certificateValidationDate]);

            Domains::where('name', $request->subdomain)->delete();
            return redirect()->route("certificate-details", $certificate->id)->with('success', $request->subdomain . ' domain deleted from certificate');
        } elseif ($generateCertificate->errorMessage != null) {
            return redirect()->route("certificate-details", $certificate->id)->with('error', $generateCertificate->errorMessage);
        } else {
            return redirect()->route("certificate-details", $certificate->id)->with('error', $request->subdomain . ' domain could not be deleted from certificate');
        }
    }

    public function removeFiles($domain)
    {
        $path = env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain;

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
