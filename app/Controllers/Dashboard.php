<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class Home extends BaseController
{

    public function dashboard()
    {
        if (!$this->session->has('user') && !$this->session->has('token')) {
            return redirect()->to('/login');
        }

        $user_model = new UserModel();

        // Pagination setup
        $page = $this->request->getVar('page') ?? 1;  // Default to page 1 if no page is set
        $perPage = 8;  // Define how many users per page
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

        // MongoDB users fetch logic (fetch only once, before looping through pages)
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

                // Map MongoDB data to users
                foreach ($users as $index => $row) {
                    // Check if a corresponding MongoDB user exists based on email
                    $mongoUser = null;
                    foreach ($mongo_users->getUsers as $mongoUserData) {
                        if ($mongoUserData->email === $row->email) {
                            $mongoUser = $mongoUserData;
                            break;
                        }
                    }

                    // If a match is found, assign the mongoId, else null
                    $users[$index]->mongoId = $mongoUser ? $mongoUser->_id : null;
                }
            } else {
                log_message('error', 'Node.js API returned status code: ' . $response->getStatusCode());
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
}
