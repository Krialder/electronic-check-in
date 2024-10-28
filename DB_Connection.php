<?php
$host = '192.168.2.150';
$db_name = 'kde_test2';
$username = 'kde';
$password = 'kde';

/**
 * Establishes a connection to the MySQL database using PDO.
 * 
 * This block of code attempts to create a new PDO instance to connect to the database
 * with the provided host, database name, username, and password. It also sets the 
 * error mode attribute to throw exceptions in case of an error.
 * 
 * @throws PDOException if the connection fails
 */
try 
{
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} 
catch (PDOException $e) 
{
    error_log('Connection failed: ' . $e->getMessage());
    die('Connection failed: ' . $e->getMessage());
}
?>