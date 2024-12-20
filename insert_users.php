<?php
require 'DB_Connection.php';

// Users data
$users = 
[
    ['name' => 'test', 'email' => 'john.doe@example.com', 'phone' => '1234567890', 'rfid_tag' => 'RFID123456', 'role' => 'admin', 'password' => '1'],
    ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'phone' => '0987654321', 'rfid_tag' => 'RFID654321', 'role' => 'user', 'password' => 'password456']
];

foreach ($users as $user) 
{
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO Users (name, email, phone, rfid_tag, role, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $user['name']);
    $stmt->bindParam(2, $user['email']);
    $stmt->bindParam(3, $user['phone']);
    $stmt->bindParam(4, $user['rfid_tag']);
    $stmt->bindParam(5, $user['role']);
    $stmt->bindParam(6, $hashed_password);
    $stmt->execute();
    $stmt->closeCursor();
}

$conn = null;
?>