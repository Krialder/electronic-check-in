<?php
$host = 'localhost';
$db = 'your_database';
$user = 'your_username';
$pass = 'your_password';
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_SSL_CA => '/path/to/ca-cert.pem',
];
try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage());
    exit('Database connection failed');
}
?>