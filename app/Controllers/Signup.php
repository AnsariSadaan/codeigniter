<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class Signup extends BaseController
{

public function signup()
    {
        if ($this->session->has('user')) {
            return redirect()->to('/dashboard');
        }
        if (isset($_POST['name'])) {
            $user_model = new UserModel();
            $data = [
                'name' => $this->request->getPost('name'),
                'age' => $this->request->getPost('age'),
                'qualification' => $this->request->getPost('qualification'),
                'email' => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT)
            ];
            $result = $user_model->save($data);
            if ($result) {
                $client = new Client();
                $nodeApiUrl = 'http://localhost:3000/api/register';
                try {
                    $response = $client->post($nodeApiUrl, [
                        'json' => [
                            'name' => $data['name'],
                            'age' => $data['age'],
                            'qualification' => $data['qualification'],
                            'email' => $data['email'],
                            'password' => $this->request->getPost('password')
                        ]
                    ]);
                    return redirect()->to('/login')->with('success', 'Registration successful! Please log in.');
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Error connecting to node.js server', $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', 'Failed to register. Please try again.');
            }
        }
        return view('signup');
    }

}