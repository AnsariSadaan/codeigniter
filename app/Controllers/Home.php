<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class Home extends BaseController
{
    public function index(): string
    {
        return view('login');
    }
    public function dashboard()
    {
        if (!$this->session->has('user') && !$this->session->has('token')) {
            return redirect()->to('/login');
        }

        $user_model = new UserModel();
        $users = $user_model->findAll();

        try {
            $client = new Client();
            $response = $client->get('http://localhost:3000/api/dashboard', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->session->get('token')
                ]
            ]);
            if ($response->getStatusCode() === 200) {
                $mongo_users = json_decode($response->getBody()->getContents());
                // echo "<pre>";
                // print_r($mongo_users); die;
                // echo "</pre>";
                for ($i = 0; $i < count($users); $i++) {
                    if ($mongo_users->getUsers[$i]->email === $users[$i]->email) {
                        $users[$i]->mongoId = $mongo_users->getUsers[$i]->_id;
                    }
                }
                // echo "<pre>";
                // print_r($users); die;
                // echo "</pre>";
                return view('dashboard', ['users' => $users]);
            } else {
                log_message('error', 'Node.js API returned status code: ' . $response->getStatusCode());
                $mongo_users = [];
            }
        } catch (\Throwable $e) {
            echo "unable to get data from mongo";
            throw $e;
        }
    }


    public function signup()
    {
        if ($this->session->has('user')) {
            return redirect()->to('/dashboard');
        }
        if (isset($_POST['name'])) {
            $user_model = new UserModel();
            $data = [
                'name' => $this->request->getPost('name'),
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
                            'email' => $data['email'],
                            'password' => $this->request->getPost('password')
                        ]
                    ]);
                    if ($response->getStatusCode() === 200) {
                        return redirect()->to('/login')->with('success', 'Registration successful! Please log in.');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Error connecting to node.js server', $e->getMessage());
                }
            } else {
                return redirect()->back()->with('error', 'Failed to register. Please try again.');
            }
        }
        return view('signup');
    }

    // public function login()
    // {
    //     if ($this->session->has('user')) {
    //         return redirect()->to('/dashboard');
    //     }
    //     if (isset($_POST['email'])) {
    //         $user_model = new UserModel();
    //         $email = $this->request->getPost('email');
    //         $password = $this->request->getPost('password');
    //         $user = $user_model->where('email', $email)->first();
    //         if ($user) {
    //             if (password_verify($password, $user->password)) {
    //                 $this->session->set("user", $user);
    //                 return redirect()->to('/dashboard')->with('success', 'Login successful!');
    //             } else {
    //                 return redirect()->back()->with('error', 'Invalid password. Please try again.');
    //             }
    //         } else {

    //             $client = new Client();
    //             $nodeApiUrl = 'http://localhost:3000/api/login';
    //             try {
    //                     $response = $client->post($nodeApiUrl, [
    //                         'json'=> [
    //                             'email'=>$email,
    //                             'password'=>$password
    //                         ]
    //                         ]);

    //                         print_r($response);

    //                         if($response->getStatusCode() === 200){
    //                             $userData = json_decode($response->getBody()->getContents(), true);
    //                             // print_r( $userData); die;
    //                             // $this->session->set("user", $userData['user']);
    //                             $this->session->set("token", $userData->token);
    //                             print_r($this->session->get('token')); die;
    //                             // print_r($response); die;
    //                             return redirect()->to('/dashboard')->with('success', 'Login successful!');
    //                         }else {
    //                             return redirect()->back()->with('error', 'Invalid email or password. Please try again.');
    //                         }
    //                 } catch (\Exception $e) {
    //                     echo $e;
    //                     // return redirect()->back()->with('error', 'Error connecting to node.js server', $e->getMessage());
    //                 }
    //             return redirect()->back()->with('error', 'Invalid email. Please try again.');
    //         }
    //     }
    //     return view('login');
    // }

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
                    return redirect()->back()->with('error', 'Error connecting to node.js server: ' . $e->getMessage());
                }
            }
        }

        return view('login');
    }


    public function logout()
    {
        $this->session->remove('token');
        $this->session->remove('user');
        $client = new Client();
        $nodeApiUrl = 'http://localhost:3000/api/logout';
        try {
            $client->post($nodeApiUrl);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error connecting to node.js server', $e->getMessage());
        }
        $this->session->setFlashdata('success', 'You have logged out successfully.');
        return redirect()->to('/login');
    }


    public function updateUser()
    {
        $user_model = new UserModel();

        // Get the submitted data
        $id = $this->request->getPost('id');
        $mongoId = $this->request->getPost('mongoId');
        // print( $mongoId) ; die;// Get MongoDB ID from form
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');

        // Prepare data for update in MySQL
        $updatedData = [];
        if ($name) $updatedData['name'] = $name;
        if ($email) $updatedData['email'] = $email;

        // Step 1: Update the user in MySQL
        $result = $user_model->update($id, $updatedData);

        if ($result) {
            // Step 2: If MySQL update is successful, update MongoDB via Node.js API
            try {
                $client = new Client();

                // Send the POST request to the Node.js API to update MongoDB
                $response = $client->post('http://localhost:3000/api/update', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->session->get('token'),
                    ],
                    'json' => [
                        'id' => $mongoId,  // MongoDB ID
                        'name' => $name,
                        'email' => $email
                    ]
                ]);
                return view('/dashboard');
            } catch (\Exception $e) {
                // echo "<pre>";
                // print_r($e); die;
                // echo "</pre>";
                // echo "unable to update mongoDB";
                return redirect()->to('/dashboard')->with('error', 'Unable to edit the MongoDB data');
            }
        } else {
            // If MySQL update fails
            return redirect()->to('/dashboard')->with('error', 'Failed to update the user data in the database!');
        }
    }




    public function deleteUser($id)
    {
        $user_model = new UserModel();

        // Delete the user from the database
        $user_model->delete($id);

        // Redirect to the dashboard with a success message
        return redirect()->to('/dashboard')->with('success', 'User deleted successfully!');
    }
}
