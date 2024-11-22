<?php
$host = '192.168.2.150';
$db_name = 'kde_test2';
$username = 'kde';
$password = 'kde';

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