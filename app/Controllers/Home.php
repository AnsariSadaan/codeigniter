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

        // Pagination setup
        $page = $this->request->getVar('page') ?? 1;  // Default to page 1 if no page is set
        $perPage = 5;  // Define how many users per page
        $offset = ($page - 1) * $perPage;  // Offset for the SQL query

        // Get search query from URL
        $searchQuery = $this->request->getVar('searchQuery') ?? '';

        // Apply search filter
        if ($searchQuery) {
            // If search query is set, filter by name (or other fields)
            $users = $user_model->like('name', $searchQuery)
                ->orderBy('id', 'ASC')
                ->findAll($perPage, $offset);
        } else {
            $users = $user_model->orderBy('id', 'ASC')
                ->findAll($perPage, $offset);
        }

        // Get the total number of users for pagination
        $totalUsers = $user_model->countAll();

        // Calculate the number of pages
        $totalPages = ceil($totalUsers / $perPage);

        // MongoDB users fetch logic (same as you already have)
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
                foreach ($users as $index => $row) {
                    if (isset($mongo_users->getUsers[$index]) && $mongo_users->getUsers[$index]->email === $row->email) {
                        $users[$index]->mongoId = $mongo_users->getUsers[$index]->_id;
                    } else {
                        // Set mongoId as null if not found
                        $users[$index]->mongoId = null;
                    }
                }
            } else {
                log_message('error', 'Node.js API returned status code: ' . $response->getStatusCode());
                $mongo_users = [];
            }
        } catch (\Throwable $e) {
            echo "Unable to get data from MongoDB.";
            throw $e;
        }

        // Pass paginated users, search query, and total pages to the view
        return view('dashboard', [
            'users' => $users,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'searchQuery' => $searchQuery
        ]);
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





    public function deleteUser($id, $mongoId)
    {
        $user_model = new UserModel();

        // Delete the user from the relational database (MySQL, etc.)
        $result = $user_model->delete($id);

        if ($result) {
            try {
                // Send DELETE request to your Node.js backend to delete from MongoDB
                $client = new Client();
                $response = $client->delete('http://localhost:3000/api/delete', [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->session->get('token'),
                    ],
                    'json' => [
                        '_id' => $mongoId,  // MongoDB ID to be deleted
                    ]
                ]);

                // Check if the MongoDB delete was successful
                $responseData = json_decode($response->getBody()->getContents(), true);
                if (isset($responseData['message']) && $responseData['message'] == 'User deleted successfully') {
                    // Success
                    return redirect()->to('/dashboard')->with('success', 'User deleted successfully!');
                } else {
                    // If MongoDB deletion failed, you can either handle that or inform the user.
                    return redirect()->to('/dashboard')->with('error', 'Error deleting user from MongoDB.');
                }
            } catch (\Exception $e) {
                // Handle error
                return redirect()->to('/dashboard')->with('error', 'Error deleting user from MongoDB: ' . $e->getMessage());
            }
        } else {
            // If deleting from MySQL failed
            return redirect()->to('/dashboard')->with('error', 'Error deleting user from database.');
        }
    }
}
