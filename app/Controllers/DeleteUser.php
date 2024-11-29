<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class DeleteUser extends BaseController
{
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
