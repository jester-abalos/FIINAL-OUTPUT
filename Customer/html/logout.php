<?php
session_start();
require '../../connection/connection.php'; // MongoDB connection setup

use MongoDB\Client;

$client = new Client("mongodb://localhost:27017");
$collection = $client->GADGETHUB->users;

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Update the status to 'offline'
    $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($userId)],
        ['$set' => ['status' => 'offline']]
    );

    // Optionally, clear the session data related to user
    unset($_SESSION['user_id']);
}

// Destroy the session and redirect to the login page
session_destroy();
header("Location: Login.php");
exit;
?>
