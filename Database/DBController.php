<?php

namespace SimpleAPI\Database;

use PDO;
use PDOException;

class DBController
{
    private static function connect()
    {
        $host = $_ENV["DB_HOST"];
        $name = $_ENV["DB_NAME"];
        $user = $_ENV["DB_USERNAME"];
        $pass = $_ENV["DB_PASSWORD"];

        try {
            $conn = new PDO("mysql:host=$host;dbname=$name", $user, $pass);
            $conn->exec("set names utf8mb4");
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }

        return $conn;
    }

    public static function query(string $query)
    {
        $stmt = self::connect()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }
}
