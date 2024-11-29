<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class Login extends BaseController
{
    public function login()
    {
        if ($this->session->has('user')) {
            return redirect()->to('/dashboard');
        }

        if (isset($_POST['email'])) {
            $user_model = new UserModel();
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $user = $user_model->where('email', $email)->first();
            $token = $this->request->getPost('token');
            if ($user && $token) {
                if (password_verify($password, $user->password)) {
                    $this->session->set("user", $user);
                    return redirect()->to('/dashboard')->with('success', 'Login successful!');
                } else {
                    return redirect()->back()->with('error', 'Invalid password. Please try again.');
                }
            } else {
                $client = new Client();
                $nodeApiUrl = 'http://localhost:3000/api/login';

                try {
                    // Sending email and password to Node.js API
                    $response = $client->post($nodeApiUrl, [
                        'json' => [
                            'email' => $email,
                            'password' => $password
                        ]
                    ]);

                    // Check if response was successful
                    if ($response->getStatusCode() === 200) {
                        // Decode the response body into an array
                        $userData = json_decode($response->getBody()->getContents(), true);

                        // Check if the token exists in the response
                        if (isset($userData['token'])) {
                            // Set the token in the session
                            $this->session->set("token", $userData['token']);  // Correct token access
                            // Set the user data in the session
                            $this->session->set("user", $userData['user']);
                        } else {
                            return redirect()->back()->with('error', 'Token not received from Node.js API.');
                        }

                        // Redirect to the dashboard with success message
                        return redirect()->to('/dashboard')->with('success', 'Login successful!');
                    } else {
                        return redirect()->back()->with('error', 'Invalid email or password. Please try again.');
                    }
                } catch (\Exception $e) {
                    // return redirect()->back()->with('error', 'Error connecting to node.js server: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Invalid email or password. Please try again.');
                }
            }
        }

        return view('login');
    }
}
