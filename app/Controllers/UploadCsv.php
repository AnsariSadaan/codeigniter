<?php

namespace App\Controllers;

use App\Models\UserModel;
use GuzzleHttp\Client;

class UploadCsv extends BaseController
{
    public function uploadCsv()
    {
        if (!$this->session->has('user') && !$this->session->has('token')) {
            return redirect()->to('/login');
        }

        helper(['form', 'url']);

        $file = $this->request->getFile('csv_file');

        if ($file->isValid() && !$file->hasMoved()) {
            // Generate a unique name and move the file to the uploads directory
            $fileName = $file->getRandomName();
            $filePath = WRITEPATH . 'uploads/' . $fileName;

            try {
                $file->move(WRITEPATH . 'uploads/', $fileName);
            } catch (\CodeIgniter\HTTP\Exceptions\HTTPException $e) {
                return redirect()->back()->with('error', 'Failed to upload file: ' . $e->getMessage());
            }

            // Process the uploaded file
            $file = fopen($filePath, 'r');
            $userModel = new UserModel();
            $client = new Client();
            $nodeApiUrl = 'http://localhost:3000/api/register';
            $headers = fgetcsv($file); // Assuming the first row contains headers
            $errors = [];
            $invalidEntries = [];

            while (($row = fgetcsv($file)) !== FALSE) {
                $data = array_combine($headers, $row);
                //check for missing fields 
                $missigFields = [];
                foreach (['name', 'email', 'password'] as $field) {
                    if (empty(trim($data[$field]))) {
                        $missigFields[] = $field;
                    }
                }

                if (!empty($missigFields)) {
                    $data['missing_fields'] = implode(',', $missigFields);
                    $invalidEntries[] = $data;
                    continue;
                }
                // Encrypt the password
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

                // Save to MySQL
                if (!$userModel->save($data)) {
                    $errors[] = "Failed to register user with email: " . $data['email'] . " in MySQL.";
                    continue; // Skip sending to Node.js if MySQL save fails
                }

                // Send to Node.js API for MongoDB storage
                try {
                    $response = $client->post($nodeApiUrl, [
                        'json' => [
                            'name' => $data['name'],
                            'age' => $data['age'],
                            'qualification' => $data['qualification'],
                            'email' => $data['email'],
                            'password' => $row[array_search('password', $headers)] // Plain password for Node.js
                        ]
                    ]);
                    log_message('info', 'MongoDB response: ' . $response->getBody());
                } catch (\Exception $e) {
                    log_message('error', 'Failed to register user in MongoDB: ' . $e->getMessage());
                    $errors[] = 'Failed to register user in MongoDB: ' . $data['email'];
                }
            }

            fclose($file);
            //generate and download csv file with invalid entries
            if (!empty($invalidEntries)) {
                $invalidFilePath = WRITEPATH . 'uploads/invalid_entries_' . time() . '.csv';
                $output = fopen($invalidFilePath, 'w');
                $headers[] = 'missing_field';
                fputcsv($output, $headers);
                foreach ($invalidEntries as $invalidRow) {
                    fputcsv($output, $invalidRow);
                }
                fclose($output);
                return $this->response->download($invalidFilePath, null)->setFileName('invalid_entries.csv');
            }
            if (empty($errors)) {
                return redirect()->back()->with('success', 'All users registered successfully in both databases.');
            } else {
                return redirect()->back()->with('error', implode('<br>', $errors));
            }
        } else {
            return redirect()->back()->with('error', 'File upload failed.');
        }
    }

}