<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class UpdateUser extends BaseController
{
    public function updateUser()
    {
        $user_model = new UserModel();

        // Get the submitted data
        $id = $this->request->getPost('id');
        $mongoId = $this->request->getPost('mongoId');
        // print( $mongoId) ; die;// Get MongoDB ID from form
        $name = $this->request->getPost('name');
        $age = $this->request->getPost('age');
        $qualification = $this->request->getPost('qualification');
        var_dump($qualification);
        $email = $this->request->getPost('email');

        // Prepare data for update in MySQL
        $updatedData = [];
        if ($name) $updatedData['name'] = $name;
        if ($age) $updatedData['age'] = $age;
        if ($qualification) $updatedData['qualification'] = $qualification;
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
                        'age' => $age,
                        'qualification' => $qualification,
                        'email' => $email
                    ]
                ]);
                return redirect()->to('/dashboard')->with('success', 'Data Updated Successfully');
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

}