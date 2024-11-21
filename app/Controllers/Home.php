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
    // public function dashboard()
    // {
    //     if (!$this->session->has('user')) {
    //         return redirect()->to('/login');
    //     }
    //     $user_model = new UserModel();
    //     $users = $user_model->findAll();
    //     return view('dashboard', ['users' => $users]);
    // }

    public function dashboard()
{
    if (!$this->session->has('user') && (!$this->session->has('token'))) {
        return redirect()->to('/login');
    }

    // Fetch data from MySQL
    $user_model = new UserModel();
    $mysql_users = $user_model->findAll(); // MySQL data

    // Fetch data from Node.js (MongoDB)
    $client = new Client();
    $nodeApiUrl = 'http://localhost:3000/api/dashboard'; // Node.js API endpoint

    try {
        // print_r("response"); die;
        $response = $client->get($nodeApiUrl, [
            'headers' => [
                'Accept' => 'application/json',
                // Add any other required headers here
            ]
        ]);
        if ($response->getStatusCode() === 200) {
            $mongo_users = json_decode($response->getBody()->getContents(), true)['getUsers'] ?? [];
        } else {
            log_message('error', 'Node.js API returned status code: ' . $response->getStatusCode());
            $mongo_users = [];
        }
    } catch (\Exception $e) {
        // echo "<pre>";
        // print_r($e); die;
        // echo "<pre>";
        log_message('error', 'Error fetching data from Node.js API: ' . $e->getMessage());
        $mongo_users = [];
    }

    // Standardize data format for both sources
    $combined_data = [];

    // Add MySQL users (check if $mysql_users is an array of objects or associative arrays)
    foreach ($mysql_users as $user) {
        if (is_object($user)) {
            // If MySQL result is an object (stdClass), use object notation
            $combined_data[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'source' => 'MySQL'
            ];
        } else {
            // If MySQL result is an associative array, use array notation
            $combined_data[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'source' => 'MySQL'
            ];
        }
    }

    // Add MongoDB users
    foreach ($mongo_users as $user) {
        $combined_data[] = [
            'id' => (string) $user['_id'], // MongoDB ID
            'name' => $user['name'],
            'email' => $user['email'],
            'source' => 'MongoDB'
        ];
    }

    // Pass combined data to the view
    return view('dashboard', ['users' => $combined_data]);
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
            if ($user) {
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
                        $response = $client->post($nodeApiUrl, [
                            'json'=> [
                                'email'=>$email,
                                'password'=>$password
                            ]
                            ]);
                            if($response->getStatusCode() === 200){
                                $userData = json_decode($response->getBody()->getContents(), true);
                                $this->session->set("user", $userData['user']);
                                $this->session->set("token", $userData['token']);
                                return redirect()->to('/dashboard')->with('success', 'Login successful!');
                            }else {
                                return redirect()->back()->with('error', 'Invalid email or password. Please try again.');
                            }
                    } catch (\Exception $e) {
                        return redirect()->back()->with('error', 'Error connecting to node.js server', $e->getMessage());
                    }
                return redirect()->back()->with('error', 'Invalid email. Please try again.');
            }
        }
        return view('login');
    }



    public function logout()
    {
        // $this->session->remove('token');
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
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');

        // Prepare data for update
        $updatedData = [];
        if ($name) $updatedData['name'] = $name;
        if ($email) $updatedData['email'] = $email;

        // Update the user in the database
        $user_model->update($id, $updatedData);

        // Redirect to the dashboard with a success message
        return redirect()->to('/dashboard')->with('success', 'User updated successfully!');
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
