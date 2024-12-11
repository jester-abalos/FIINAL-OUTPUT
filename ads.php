<?php
// Include MongoDB and connect
require 'vendor/autoload.php'; // MongoDB client library

// MongoDB connection URI (replace with your own MongoDB Atlas connection string)
$uri = "mongodb://localhost:27017/"; // Make sure MongoDB is running locally
$client = new MongoDB\Client($uri);
$db = $client->GADGETHUB; // Database name
$adsCollection = $db->ads; // Collection name for ads

// Fetch all ads
$ads = $adsCollection->find();

// Set the response header to JSON
header('Content-Type: application/json');

// Check if ads exist and return as JSON
if ($ads) {
    echo json_encode(iterator_to_array($ads));
} else {
    // Return an empty array if no ads are found
    echo json_encode([]);
}
?>
