<?php

namespace App\Controllers;

use App\Models\SiteDataModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Firebase\JWT\JWT;




class SiteData extends ResourceController
{

    public function addSite()
    {


        $rules = [
            "user_email" => "required|valid_email|min_length[6]",
            "response_email" => "required|valid_email|min_length[6]",
            "site_url" => "required|valid_url",
        ];

        $messages = [
            "site_url" => [
                "required" => "Please enter a valid Site Url",
                "valid_url" => "Please enter a site url in the format www.example.com"
            ],
            "user_email" => [
                "required" => "User Email required",
                "valid_email" => "Invalid User Email Address"
            ],
            "response_email" => [
                "required" => "Response Email required",
                "valid_email" => "Invalid Response Email Address"
            ]

        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {

            $SiteDataModel = new SiteDataModel();

            $data = [
                "user_email" => $this->request->getVar("user_email"),
                "site_name" => $this->request->getVar("site_name"),
                "response_email" => $this->request->getVar("response_email"),
                "site_url" => $this->request->getVar("site_url")
            ];

            if ($SiteDataModel->insert($data)) {

                $response = [
                    'status' => 200,
                    "error" => false,
                    'messages' => 'Site Details Added Successfully',
                    'data' => $data
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'messages' => 'Failed to Add Site Details',
                ];
            }
            
        }
        return $this->respondCreated($response);
        
            
        
        

        // return $this->respondCreated($response);
    }

    public function getAllSites(){
        $email = Services::getEmail();
       //fetch sites from db using email
        $SiteDataModel = new SiteDataModel();

        $allSites = $SiteDataModel->where('user_email', $email)
                   ->findAll();
    
        
        $response = [
            'status' => 200,
            'error' => false,
            'messages' => 'User details',
            'data' => [
                'websites' => $allSites
            ]
        ];
        return $this->respond($response);

    }


    public function updateSite($id = null){
        helper(['form']);
        
        $email = Services::getEmail();    
    
        
        $rules = [
            "response_email" => "required|valid_email|min_length[6]",
            "site_url" => "required|valid_url",
        ];

        $messages = [
            "site_url" => [
                "required" => "Please enter a valid Site Url",
                "valid_url" => "Please enter a site url in the format www.example.com"
            ],
            
            "response_email" => [
                "required" => "Response Email required",
                "valid_email" => "Invalid Response Email Address"
            ]

        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];
        } else {

            $SiteDataModel = new SiteDataModel();

            $data = [
                "id" => $id,
                "user_email" => $email,
                "site_name" => $this->request->getVar("site_name"),
                "response_email" => $this->request->getVar("response_email"),
                "site_url" => $this->request->getVar("site_url")
            ];

            if ($SiteDataModel->save($data)) {

                $response = [
                    'status' => 200,
                    "error" => false,
                    'messages' => 'Site Data Updated Added Successfully',
                    'data' => $data
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'messages' => 'Failed to Update Site Details',
                ];
            }
            
        }
        return $this->respondCreated($response);

    }

    public function deleteSite($id = null){

        $email = Services::getEmail();
        $SiteDataModel = new SiteDataModel();

        $getSiteData = $SiteDataModel->where('user_email', $email)
                                        ->find($id);

        if($getSiteData){
            $SiteDataModel->delete($id);
            $deleted = [
                'status' => 200,
                'error' => false,
                'message' => 'Website Deleted Successfully'
            ];
            return $this->respondDeleted($deleted);
        }else{
            return $this->failNotFound('Item Not Found');
        }

    }

    public function checkssl($site_url){
        $url = $site_url;
        $orignal_parse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 
        30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        $valid_from = date(DATE_RFC2822,$certinfo['validFrom_time_t']);
        $valid_to = date(DATE_RFC2822,$certinfo['validTo_time_t']);
    

       
        $ssldetails = (object)[
            'valid_from' => $valid_from,
            'valid_to' => $valid_to,
            'full_cert_details' => $certinfo
        ];

        return $ssldetails;
    }

    public function dnsbllookup($ip)
    {
        // Add your preferred list of DNSBL's
        $dnsbl_lookup = [
            "dnsbl-1.uceprotect.net",
            "dnsbl-2.uceprotect.net",
            "dnsbl-3.uceprotect.net",
            "dnsbl.dronebl.org",
            "dnsbl.sorbs.net",
            "zen.spamhaus.org",
            "bl.spamcop.net",
            "list.dsbl.org",
            "all.s5h.net",
            "b.barracudacentral.org",
            "blacklist.woody.ch",	
            "bogons.cymru.com",	
            "cbl.abuseat.org",
            "combined.abuse.ch",
            "db.wpbl.info",	
            "drone.abuse.ch",	
            "duinv.aupads.org",
            "dul.dnsbl.sorbs.net",	
            "dyna.spamrats.com",	
            "http.dnsbl.sorbs.net",
            "ips.backscatterer.org",	
            "ix.dnsbl.manitu.net",	
            "korea.services.net",
            "misc.dnsbl.sorbs.net",	
            "noptr.spamrats.com",	
            "orvedb.aupads.org",
            "pbl.spamhaus.org",	
            "proxy.bl.gweep.ca",	
            "psbl.surriel.com",
            "relays.bl.gweep.ca",	
            "relays.nether.net",	
            "sbl.spamhaus.org",
            "singular.ttk.pte.hu",	
            "smtp.dnsbl.sorbs.net",	
            "socks.dnsbl.sorbs.net",
            "spam.abuse.ch",	
            "spam.dnsbl.anonmails.de",	
            "spam.dnsbl.sorbs.net",
            "spam.spamrats.com",	
            "spambot.bls.digibase.ca",	
            "spamrbl.imp.ch",
            "spamsources.fabel.dk",	
            "ubl.lashback.com",	
            "ubl.unsubscore.com",
            "virus.rbl.jp",	
            "web.dnsbl.sorbs.net",	
            "wormrbl.imp.ch",
            "xbl.spamhaus.org",	
            "z.mailspike.net",	
            "zombie.dnsbl.sorbs.net",
        ];

        $lookUp = [];

        if ($ip) {
            $reverse_ip = implode(".", array_reverse(explode(".", $ip)));
            foreach ($dnsbl_lookup as $host) {
                if (checkdnsrr($reverse_ip . "." . $host . ".", "A")) {
                    $listed = (object)[
                        'reverse_ip' => $reverse_ip,
                        'host' => $host,
                        'listed' => true
                    ];

                    array_push($lookUp, $listed);
                }else{
                    $listed = (object)[
                        'reverse_ip' => $reverse_ip,
                        'host' => $host,
                        'listed' => false
                    ];

                    array_push($lookUp, $listed);
                }
            }
        }

        if (empty($listed)) {
            return '"A" record was not found';
        } else {
            return $lookUp;
        }
    }


    public function checkSiteStat($id = null){
        $email = Services::getEmail();
        $SiteDataModel = new SiteDataModel();

        $getSiteData = $SiteDataModel->where('user_email', $email)
                                        ->find($id);
        
        if($getSiteData){
            $siteData = (object)$getSiteData;

            $host = $siteData->{'site_url'};
            $response_email =$siteData->{'response_email'};
        
            if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
                $check =  'online';
                fclose($socket);
            } else {
                $check = 'offline';
            }
            
            $ip_address = gethostbyname($host);  

       
            if (filter_var($ip_address, FILTER_VALIDATE_IP)) {
                $dnsLookUp = $this->dnsbllookup($ip_address);
                // $ssl_details = $this->checkssl($host);
            } else {
                $dnsLookUp = "Invalid IP";
                // $ssl_details = "Invalid Url or SSL Details";
            }
        

            $siteData =[
                'url' => $host,
                'status' => $check,
                'ip_address' => $ip_address,
                'dns_blacklist_check' => $dnsLookUp,
                // 'ssl_status' => $ssl_details
            ];

            return $this->respond($siteData);
            

        }else{
            return $this->failNotFound('Item Not Found');
        }
    }
}