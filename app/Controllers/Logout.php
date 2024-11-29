<?php

namespace App\Controllers;

// use App\Models\UserModel;
// use GuzzleHttp\Client;

class Logout extends BaseController {
    public function logout()
    {
        $this->session->remove('token');
        $this->session->remove('user');
        // $client = new Client();
        // $nodeApiUrl = 'http://localhost:3000/api/logout';
        // try {
        //     $client->post($nodeApiUrl);
        // } catch (\Exception $e) {
        //     return redirect()->back()->with('error', 'Error connecting to node.js server', $e->getMessage());
        // }
        // $this->session->setFlashdata('success', 'You have logged out successfully.');
        return redirect()->to('/login')->with("success", "You have logged out successfully.");
    }
}
