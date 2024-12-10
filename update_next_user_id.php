<?php
include 'DB_Connection.php';

try 
{
    // Calculate the next User ID
    $nextUserIdQuery = "SELECT MAX(user_id) + 1 AS next_user_id FROM Users";
    $nextUserIdStmt = $conn->prepare($nextUserIdQuery);
    $nextUserIdStmt->execute();
    $nextUserId = $nextUserIdStmt->fetch(PDO::FETCH_ASSOC)['next_user_id'] ?? 1;

    echo json_encode(['next_user_id' => $nextUserId]);
} 
catch (Exception $e) 
{
    echo json_encode(['error' => 'Error calculating next user ID: ' . $e->getMessage()]);
}
?>