<?php

namespace Encryption\Connection;


use Exception;
use mysqli;

class DatabaseConnectionExample
{
    public mysqli $mysqli;

    public function __construct()
    {
        $sql_hostname = 'localhost';
        $sql_username = 'yourusername';
        $sql_password = 'yourpassword';
        $sql_database = 'encryptiontest';

        try {
            $this->mysqli = new mysqli($sql_hostname, $sql_username, $sql_password, $sql_database);
        } catch (Exception $e) {
            header('Location: /encryption/custom-500.php');
            exit;
        }
    }
}






