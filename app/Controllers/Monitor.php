<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Monitor extends ResourceController
{
    public function basic($url){
    
        $host = $url;
        
        if($socket =@ fsockopen($host, 80, $errno, $errstr, 30)) {
        $check =  'online!';
        fclose($socket);
        } else {
        $check = 'offline.';
        }

        $siteData = (object)[
            'url' => $host,
            'status' => $check
        ];

        return $this->respond($siteData);
    }
}