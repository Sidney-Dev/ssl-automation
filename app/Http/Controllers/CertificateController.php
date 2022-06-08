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

        $certificate = new LetsEncrypt;

        $certificate->validateDomain($domain);
        $certificate->checkDomainDoesNotExist($domain);

        $email = Auth::user()->email;
        exec(env('ROOT_DIR') . "register " . $email);

        // generate the certificate and ensure to only save records in the database once the certificate is generated

        $certificate->generate($domain);

        if ($certificate->error == false) {
            LetsEncryptCertificate::create([
                'domain' =>  $domain,
                'certificate_validation_date' => $certificate->certificateValidationDate,
                'fullchain_path' =>  env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/public/fullchain.pem',
                'chain_path' => env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/public/chain.pem',
                'cert_path' => env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/public/cert.pem',
                'privkey_path' => env('MAIN_DIR') . '/.acmephp/master/certs/' . $domain . '/private/key.private.pem',
                'last_renewed_at' => now(),
                'created' => true,
                'status' => 'pending',
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
            //LetsEncryptCertificate::where('id',$id)->delete(); // it hides from showing but the record is still in the DB
            // $this->removeFiles($getSlugAndEnvId->domain); // remove directories
            return redirect('/certificates')->with('success', 'Certificate has been removed');
        }
    }

    /**
     * Delete the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function delete(Request $request)
    {
        if (LetsEncryptCertificate::where('id', $request->certificate)->delete()) {
            return redirect('/certificates')->with('success', 'Certificate has been deleted');
        } else {
            return redirect('/certificates')->with('error', 'Certificate has not been deleted');
        }
    }

    public function activate($id)
    {
        $getSlugAndEnvId = LetsEncryptCertificate::select('slug', 'environmentID')->where('id', $id)->first();

        if ($getSlugAndEnvId->slug) {
            $env = new Environments();
            $response = $env->certificateActivation($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug);

            if ($response == true) {

                if ($env->clearDomainCache($getSlugAndEnvId->environmentID, $getSlugAndEnvId->slug)) {
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
            return redirect('/certificates')->with('success', "Certificate has been installed");
        }
    }

    /**
     * This method is used to store additional domains to an existing certificate.
     * The current status of the certificate will determine the actions
     * 
     * NOTE: At this stage of the application, the user is responsible to ensure that the domains he is entering are his, and valid
     *       In another version of the application, we can perhaps link to a domain checker to ensure that the domain exists and belongs to a certain entity
     * 
     * - active:
     *      deactivate the certificate
     *      remove the certificate from acquia
     *      remove certificate from the file system
     *      generate the certificate including the additional domains
     *      add the certificate to the environment which it was
     *      put the certificate back in the active state
     * - inactive|deactivated:
     *      remove certificate from the file system
     *      generate the certificate including the additional domains
     *      add the certificate to the environment which it was
     * - pending:
     *      remove certificate from the file system
     *      generate the certificate including the additional domains         
     */
    public function storeDomains(Request $request, LetsEncryptCertificate $certificate)
    {
        $letsencrypt = new LetsEncrypt;

        // Validate domain by checking invalid characters
        $letsencrypt->validateDomain($request->domains);

        $aDomains = explode("\r\n", $request->domains);

        $env = new Environments();

        // Check if domain already exists
        foreach ($aDomains as $domain) {
            $letsencrypt->checkDomainDoesNotExist($domain);
        }

        // Check if any new domain was added
        // foreach($certificate->domains as $domain) {
        //     if (in_array($domain->name,$aDomains)) {
        //         return redirect()->route("certificate-details", $certificate->id)->with('error', 'Please insert a new domain');
        //     } 
        // }

        switch ($certificate->status) {

            case 'activated':

                $letsencrypt->renameDir($certificate->domain, "-old");
                $letsencrypt->generate($certificate->domain, $request->domains); // returns false if successful

                if ($letsencrypt->error == false) {
                    $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
                    $env->certificateDeletion($certificate->environmentID, $certificate->slug);

                    if (is_array($aDomains)) {
                        foreach ($aDomains as $domain) {
                            Domains::updateOrCreate([
                                'name' => trim($domain),
                                'lets_encrypt_certificate_id' => $certificate->id,
                            ]);
                        }
                    }

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);
                    $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first(); //go grab the updated slug by quering letsencryptcertificate model
                    $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains added to certificate');
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be added to certificate, ' . $letsencrypt->errorMessage);
                }

                break;

            case 'deactivated':
            case 'installed':

                $letsencrypt->renameDir($certificate->domain, "-old");
                $letsencrypt->generate($certificate->domain, $request->domains); // returns false if successful


                if ($letsencrypt->error == false) {
                    $env->certificateDeletion($certificate->environmentID, $certificate->slug);

                    if (is_array($aDomains)) {
                        foreach ($aDomains as $domain) {
                            Domains::updateOrCreate([
                                'name' => trim($domain),
                                'lets_encrypt_certificate_id' => $certificate->id,
                            ]);
                        }
                    }

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains added to certificate');
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be added to certificate, ' . $letsencrypt->errorMessage);
                }

                break;

            case 'pending':

                $letsencrypt->renameDir($certificate->domain, "-old");
                $letsencrypt->generate($certificate->domain, $request->domains); // returns false if successful

                if ($letsencrypt->error == false) {
                    if (is_array($aDomains)) {
                        foreach ($aDomains as $domain) {
                            Domains::updateOrCreate([
                                'name' => trim($domain),
                                'lets_encrypt_certificate_id' => $certificate->id,
                            ]);
                        }
                    }

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);

                    $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first();
                    $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains added to certificate');
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be added to certificate, ' . $letsencrypt->errorMessage);
                }

                break;

            default:
                // TODO: throw an error message;
                echo "Something went wrong";
        }

        return redirect()->back();
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
        $letsencrypt = new LetsEncrypt();

        switch ($certificate->status) {
            case 'activated':

                $letsencrypt->renameDir($certificate->domain, "-old");

                $newCertificate = $letsencrypt->generate($certificate->domain, $newDomains); // returns false if successful

                if ($letsencrypt->error == false) {
                    $env->certificateDeactivation($certificate->environmentID, $certificate->slug);
                    $env->certificateDeletion($certificate->environmentID, $certificate->slug);

                    if (is_array($newCertificate)) {
                        foreach ($newCertificate as $domain) {
                            Domains::where([
                                'name' => trim($domain),
                                'lets_encrypt_certificate_id' => $certificate->id,
                            ])->delete();
                        }
                    }

                    if (empty($newDomains) && $newCertificate === true) {
                        Domains::where([
                            'lets_encrypt_certificate_id' => $certificate->id,
                        ])->delete();
                    }

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);
                    $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first(); //go grab the updated slug by quering letsencryptcertificate model
                    $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains deleted from certificate');
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be deleted from certificate, ' . $letsencrypt->errorMessage);
                }

                break;

            case 'deactivated':
            case 'installed':
                
                $letsencrypt->renameDir($certificate->domain, "-old");
                $newCertificate = $letsencrypt->generate($certificate->domain, $newDomains); // returns false if successful

                if (isset($letsencrypt->error) && $letsencrypt->error == false) {
                    $env->certificateDeletion($certificate->environmentID, $certificate->slug); // delete cert from acquia environment

                    if (is_array($newCertificate)) {
                        foreach ($newCertificate as $domain) {
                            Domains::where([
                                'name' => trim($domain),
                                'lets_encrypt_certificate_id' => $certificate->id,
                            ])->delete();
                        }
                    }

                    if (empty($newDomains) && $newCertificate === true) {
                        Domains::where([
                            'lets_encrypt_certificate_id' => $certificate->id,
                        ])->delete();
                    }

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains deleted from certificate');
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be deleted from certificate, ' . $letsencrypt->errorMessage);
                }


                break;

            case 'pending':

                $letsencrypt->renameDir($certificate->domain, "-old");
                $newCertificate = $letsencrypt->generate($certificate->domain, $newDomains); // returns false if successful

                if (isset($letsencrypt->error) && $letsencrypt->error == false) {
                    if (is_array($newCertificate)) {
                        foreach ($newCertificate as $domain) {
                            Domains::where([
                                'name' => trim($domain),
                                'lets_encrypt_certificate_id' => $certificate->id,
                            ])->delete();
                        }
                    }

                    if (empty($newDomains) && $newCertificate === true) {
                        Domains::where([
                            'lets_encrypt_certificate_id' => $certificate->id,
                        ])->delete();
                    }

                    $env->addCertificateToEnvironment($certificate->label, $certificate->environmentID, $certificate->domain);

                    $updatedCertificate = LetsEncryptCertificate::where('domain', $certificate->domain)->first();
                    $env->certificateActivation($updatedCertificate->environmentID, $updatedCertificate->slug);

                    $letsencrypt->removeDir($certificate->domain . "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('success', 'Domains deleted from certificate');
                } else {
                    $letsencrypt->removeDir($certificate->domain);
                    $letsencrypt->renameDir($certificate->domain, "-old");
                    return redirect()->route("certificate-details", $certificate->id)->with('error', 'Domains could not be deleted from certificate, ' . $letsencrypt->errorMessage);
                }

                break;

            default:
                // TODO: throw an error message;
                echo "Something went wrong";
        }

        return redirect()->back();
    }
}
