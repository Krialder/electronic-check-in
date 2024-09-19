<?php
include 'DB_Connection.php';

// Get RFID code from POST request
$rfid = $_POST['rfid'];

// Prepare and bind
$stmt = $conn->prepare("SELECT user_id FROM Users WHERE rfid_tag = ?");
$stmt->bind_param("s", $rfid);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) 
{
    // RFID exists, log the user
    $stmt->bind_result($user_id);
    $stmt->fetch();

    // Insert into AccessLogs
    $stmt_insert = $conn->prepare("INSERT INTO AccessLogs (user_id, rfid_tag, device_id, status) VALUES (?, ?, ?, ?)");
    $device_id = 1; // Assuming device_id is 1 for this example
    $status = "Logged In";
    $stmt_insert->bind_param("isis", $user_id, $rfid, $device_id, $status);
    $stmt_insert->execute();

    echo "User logged in successfully";
} 
else 
{
    echo "RFID not recognized";
}

$stmt->close();
$conn->close();
?>
