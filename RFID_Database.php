<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include 'DB_Connection.php';

if (isset($_POST['rfid'])) 
{
    $rfid = $_POST['rfid'];
    echo "Received RFID: $rfid";

    // Check if the RFID tag exists in the Users table
    $sql = "SELECT user_id FROM Users WHERE rfid_tag = :rfid_tag";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':rfid_tag', $rfid);
    $stmt->execute();

    if ($stmt->rowCount() > 0) 
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'];

        // Log the access in the AccessLogs table
        $sql = "INSERT INTO AccessLogs (user_id, rfid_tag, device_id, status) VALUES (:user_id, :rfid_tag, :device_id, :status)";
        $stmt = $conn->prepare($sql);
        $device_id = 1;
        $status = 'granted';
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':rfid_tag', $rfid);
        $stmt->bindParam(':device_id', $device_id);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        echo "Access granted";
    } 
    else 
    {
        echo "Access denied";
    }
}

// Handle auto-logout
if (isset($_POST['auto_logout'])) 
{
    echo "Auto-logout successful";
}

$conn = null;
?>