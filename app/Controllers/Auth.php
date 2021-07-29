<?php namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Firebase\JWT\JWT;

class Auth extends ResourceController
{

	protected $format = 'json';

	public function login()
	{

        $rules = [
            "email" => "required|valid_email|min_length[6]",
            "password" => "required",
        ];

        $messages = [
            "email" => [
                "required" => "Email required",
                "valid_email" => "Email address is not in format"
            ],
            "password" => [
                "required" => "password is required"
            ],
        ];

        if (!$this->validate($rules, $messages)) {

            $response = [
                'status' => 500,
                'error' => true,
                'message' => $this->validator->getErrors(),
                'data' => []
            ];

            return $this->respondCreated($response);
            
        } else {
            $userModel = new UserModel();

            $userdata = $userModel->where("email", $this->request->getVar("email"))->first();

            if (!empty($userdata)) {

                if (password_verify($this->request->getVar("password"), $userdata['password'])) {

                    $key        = Services::getKey();
                    $authHeader = $this->request->getServer('Authorization');
                    // $arr        = explode(' ', $authHeader);
                    $token      = $authHeader;

                    $iat = time(); // current timestamp value
                    $nbf = $iat + 10;
                    $exp = $iat + 3600;

                    $payload = array(
                        "iss" => "The_claim",
                        "aud" => "The_Aud",
                        "iat" => $iat, // issued at
                        "nbf" => $nbf, //not before in seconds
                        "exp" => $exp, // expire time in seconds
                        "data" => $userdata,
                    );

                    $token = JWT::encode($payload, $key);
                    
                    // $decoded = JWT::decode($token, $key, array("HS256"));

                    

                    $response = [
                        'status' => 200,
                        'error' => false,
                        'messages' => 'User logged In successfully',
                        'data' => [
                            // 'profile' => $userdata,
                            'token' => $token
                        ]
                    ];
                    return $this->respondCreated($response);
                    
                } else {

                    $response = [
                        'status' => 500,
                        'error' => true,
                        'messages' => 'Incorrect details',
                        'data' => []
                    ];
                    return $this->respondCreated($response);
                }
            } else {
                $response = [
                    'status' => 500,
                    'error' => true,
                    'messages' => 'User not found',
                    'data' => []
                ];
                return $this->respondCreated($response);
            }
        }

		return $this->respond(['message' => 'Invalid login details'], 401);
	}

}