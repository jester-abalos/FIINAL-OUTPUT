<?php
class DB {
    private $client;
    private $db;

    public function __construct() {
        $this->client = new MongoDB\Client("mongodb://localhost:27017");
        $this->db = $this->client->GADGETHUB; // Database name
    }

    public function getCollection($adscollectionName) {
        return $this->GADGETHUB->$adscollectionName;
    }
}
?>
