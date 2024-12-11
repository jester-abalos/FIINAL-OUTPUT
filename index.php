<?php
require 'vendor/autoload.php'; // Include MongoDB client library

// Connect to MongoDB
$client = new MongoDB\Client("mongodb://localhost:27017");
$db = $client->ecommerce;  // Your database name
$adsCollection = $db->ads;  // Your ads collection

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
