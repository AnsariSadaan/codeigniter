<?php

function generateRandomName($usedNames) {
    $firstNames = ["abc"];
    $lastNames = ["Shaikh"];
    
    do {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $name = $firstName . " " . $lastName;
    } while (in_array($name, $usedNames));
    
    $usedNames[] = $name;
    return [$name, $usedNames];
}

// Function to generate a random email
function generateRandomEmail($name, $usedEmails) {
    $domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'icloud.com'];
    $nameParts = explode(" ", $name);
    do {
        $email = strtolower($nameParts[0]) . '.' . strtolower($nameParts[1]) . '@' . $domains[array_rand($domains)];
    } while (in_array($email, $usedEmails));
    
    $usedEmails[] = $email;
    return [$email, $usedEmails];
}

// Function to generate a random password (6 characters)
function generateRandomPassword() {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 6);
}

// Function to generate a random age between 18 and 60
function generateRandomAge() {
    return rand(18, 60);
}

// Function to generate a random qualification
function generateRandomQualification() {
    $qualifications = ["Engineer", "Doctor", "Rider", "Bachelor", "Lawer", "Nurse"];
    return $qualifications[array_rand($qualifications)];
}

// Open the CSV file for writing
$file = fopen("fake_data.csv", "w");

// Write the header row
fputcsv($file, ['name', 'age', 'qualification', 'email', 'password']);

// Initialize arrays to track used names and emails
$usedNames = [];
$usedEmails = [];

// Generate 500 rows of data
for ($i = 0; $i < 2; $i++) {
    // Generate unique name and email
    list($name, $usedNames) = generateRandomName($usedNames);
    list($email, $usedEmails) = generateRandomEmail($name, $usedEmails);
    $age = generateRandomAge();
    $qualification = generateRandomQualification();
    $password = generateRandomPassword();

    // Write the data row to the CSV file
    fputcsv($file, [$name, $age, $qualification, $email, $password]);
}

// Close the file
fclose($file);

echo "CSV file generated successfully with 500 unique rows of data!";
?>
