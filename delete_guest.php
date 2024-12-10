<?php
session_start();

include 'DB_Connection.php';

// Check if the user role is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') 
{
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);
error_log('Received JSON input: ' . json_encode($data)); // Debugging statement
$userId = $data['user_id'] ?? null;

if ($userId === null) 
{
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try 
{
    // Delete the guest from the Guest table
    $sql = 'DELETE FROM Guest WHERE user_id = :user_id';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();

    reassignGuestIds($conn); // Reassign Guest IDs

    echo json_encode(['success' => true]);
} 
catch (Exception $e) 
{
    echo json_encode(['success' => false, 'message' => 'Error deleting guest: ' . $e->getMessage()]);
}

// Function to reassign Guest IDs sequentially
function reassignGuestIds($conn) 
{
    $stmt = $conn->query('SELECT user_id FROM Guest ORDER BY user_id');
    $guestIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $newId = 1;
    foreach ($guestIds as $id) 
    {
        if ($id != $newId) 
        {
            $updateStmt = $conn->prepare('UPDATE Guest SET user_id = :new_id WHERE user_id = :old_id');
            $updateStmt->bindParam(':new_id', $newId);
            $updateStmt->bindParam(':old_id', $id);
            $updateStmt->execute();
        }
        $newId++;
    }
}
?>