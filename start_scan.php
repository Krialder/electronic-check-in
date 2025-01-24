<?php
// Include the database connection
include 'DB_Connection.php';

try 
{
    // Simulate RFID scanning process
    $rfidTag = isset($_POST['rfid_tag']) ? $_POST['rfid_tag'] : null;
    if (!$rfidTag) 
    {
        throw new Exception('RFID tag not provided');
    }

    // Check if RFID tag exists in Users table
    $stmt = $conn->prepare("SELECT user_id FROM Users WHERE rfid_tag = :rfid_tag");
    $stmt->bindParam(':rfid_tag', $rfidTag);
    $stmt->execute();

    if ($stmt->rowCount() > 0) 
    {
        header("Access-Control-Allow-Origin: *");
        echo json_encode(['status' => 'error', 'message' => 'RFID tag already in use']);
    } 
    else 
    {
        // Insert RFID tag into Guest table
        $stmt = $conn->prepare("INSERT INTO Guest (rfid_tag) VALUES (:rfid_tag)");
        $stmt->bindParam(':rfid_tag', $rfidTag);
        $stmt->execute();

        // Return the RFID tag
        header("Access-Control-Allow-Origin: *");
        echo json_encode(['status' => 'success', 'rfid_tag' => $rfidTag]);
    }
} 
catch (PDOException $e) 
{
    header("Access-Control-Allow-Origin: *");
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
catch (Exception $e) 
{
    header("Access-Control-Allow-Origin: *");
    error_log('Error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
