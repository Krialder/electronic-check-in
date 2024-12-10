<?php
// Include the database connection
include 'DB_Connection.php';

try 
{
    // Simulate RFID scanning process
    $rfidTag = "RFID123456"; // Replace this with actual RFID scanning logic

    // Check if RFID tag exists in Users table
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE rfid_tag = :rfid_tag");
    $stmt->bindParam(':rfid_tag', $rfidTag);
    $stmt->execute();

    if ($stmt->rowCount() > 0) 
    {
        echo json_encode(['status' => 'error', 'message' => 'RFID tag already in use']);
    } 
    else 
    {
        // Insert RFID tag into Guest table
        $stmt = $conn->prepare("INSERT INTO Guest (rfid_tag) VALUES (:rfid_tag)");
        $stmt->bindParam(':rfid_tag', $rfidTag);
        $stmt->execute();

        // Return the RFID tag
        echo json_encode(['status' => 'success', 'rfid_tag' => $rfidTag]);
    }
} 
catch (PDOException $e) 
{
    error_log('Connection failed: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
}
?>
