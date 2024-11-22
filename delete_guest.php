<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include 'DB_Connection.php';

// Check if user_id is set in the POST parameters
if (isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    // Prepare and execute the delete statement
    $sql = 'DELETE FROM Guest WHERE user_id = :user_id';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo 'Guest deleted successfully!';
    } else {
        echo 'Error deleting guest.';
    }
} else {
    echo 'No user ID specified.';
}

// Close connection
$conn = null;
?>