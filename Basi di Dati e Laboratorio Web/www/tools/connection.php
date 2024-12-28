<?php

$dsn = DB_TYPE . ":host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4";

try {
    $db_connection = new PDO($dsn, DB_USER, DB_PASSWORD);

    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    throw new Exception('Impossibile connettersi al database: ' . $e->getMessage());
}
