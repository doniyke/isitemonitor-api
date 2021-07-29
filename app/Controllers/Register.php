<?php

namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;
use Exception;
class Register extends ResourceController
{

	public function index()
    {
        $rules = [
            "name" => "required",
            "email" => "required|valid_email|is_unique[users.email]|min_length[6]",
            "password" => "required",
        ];

        $messages = [
            "name" => [
                "required" => "Name is required"
            ],
            "email" => [
                "required" => "Email required",
                "valid_email" => "Invalid Email Address",
                "is_unique" => "A User With This Email Already Exist"
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
        } else {

            $userModel = new UserModel();

            $data = [
                "name" => $this->request->getVar("name"),
                "email" => $this->request->getVar("email"),
                "password" => password_hash($this->request->getVar("password"), PASSWORD_DEFAULT),
            ];

            if ($userModel->insert($data)) {

                $response = [
                    'status' => 200,
                    "error" => false,
                    'messages' => 'User Registration Successful',
                    'data' => []
                ];
            } else {

                $response = [
                    'status' => 500,
                    "error" => true,
                    'messages' => 'Failed to create user',
                    'data' => []
                ];
            }
        }

        return $this->respondCreated($response);
    }
}
