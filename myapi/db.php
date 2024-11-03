<?php

class Database {
    private $dsn = 'mysql:host=localhost;dbname=api_db;charset=utf8';
    private $username = "root"; 
    private $password = "";
    public $conn;

    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Function to connect to the database
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            echo json_encode(["error" => "Connection error!"]);
            http_response_code(500); // Internal Server Error
            exit();
        }

        return $this->conn;
    }
}