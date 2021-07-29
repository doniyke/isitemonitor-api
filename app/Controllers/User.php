<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Firebase\JWT\JWT;


class User extends ResourceController
{
    protected $format = 'json';
    
    public function details()
    {
        
        $response = [
            'status' => 200,
            'error' => false,
            'messages' => 'User details',
            'data' => [
                'profile' => 'hello'
            ]
        ];
        return $this->respondCreated($response);
    }

    
    
}