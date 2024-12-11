<?php
require '../../vendor/autoload.php'; // Composer autoload


$client = new MongoDB\Client("mongodb://localhost:27017/");

$userCollection = $client->GADGETHUB->users;
$cartCollection = $client->GADGETHUB->carts;
$productCollection = $client->GADGETHUB->products;
$admincollection = $client->GADGETHUB->admin;
$ordersCollection = $client->GADGETHUB->orders; 
?>
