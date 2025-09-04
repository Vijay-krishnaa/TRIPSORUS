<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "Tripsorus";

$dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

// Development-specific settings
define('ENVIRONMENT', 'development');

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // Development-only additional options
        PDO::MYSQL_ATTR_LOCAL_INFILE => true, // Only for dev imports
        PDO::ATTR_PERSISTENT => false, // Better for dev (no connection pooling)
    ]);
    
    // Development-only settings
    if (ENVIRONMENT === 'development') {
        $pdo->exec("SET SESSION sql_mode = ''"); // Relaxed mode for dev
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

} catch (PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("DEV ERROR: " . $e->getMessage() . " (Check your connection settings)");
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("Connection error. Please try again later.");
    }
}