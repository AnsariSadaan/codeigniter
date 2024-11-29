<?php

namespace App\Controllers;
use App\Models\UserModel;

class DownloadCsv extends BaseController
{ 
public function downloadCSV()
    {
        // Load the User model
        $userModel = new UserModel();

        // Fetch data from the database
        $users = $userModel->findAll();

        // Prepare CSV data
        $csvData = [];

        // Add header row
        $csvData[] = ['ID', 'Name', 'Age', 'Qualification', 'Email']; // Adjust according to your table structure

        // Loop through the users and add to CSV data
        foreach ($users as $user) {
            $csvData[] = [
                $user->id,            // Assuming 'id' is your primary key
                $user->name,
                $user->age,
                $user->qualification,
                $user->email
            ];
        }

        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="users_data.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Output each row of the CSV
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }

        // Close output stream
        fclose($output);
        exit();
    }

}