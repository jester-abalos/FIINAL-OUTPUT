<?php
require '../../vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->GADGETHUB;
$adminCollection = $database->selectCollection('admin');

$adminUsername = "testacc";
$plainPassword = "1234";
$email = "123@test.com";

$hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

$account = [
    "email" => $email,
    "password" => $hashedPassword,
    "adminUsername" => $adminUsername,  
    "verified" => true,
    "loginStatus" => true,
    "lastLogin" => "",
    
];

try {
    $insertResult = $adminCollection->insertOne($account);

    echo "Account inserted successfully.<br>";
    echo "Inserted Account Details:<br>";
    echo "Admin Username: " . $adminUsername . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Verification Status: " . ($account['verified'] ? "Verified" : "Not Verified") . "<br>";
    echo "Login Status: " . ($account['loginStatus'] ? "Active" : "Inactive") . "<br>";
    echo "Last Login: " . ($account['lastLogin'] ?: "N/A") . "<br>";
    echo "Inserted ID: " . $insertResult->getInsertedId() . "<br>";
} catch (Exception $e) {
    echo "Error inserting account: " . $e->getMessage();
}
?>
