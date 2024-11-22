<?php
// Start the session to access session variables
session_start(); 
// Include the database connection file
include 'DB_Connection.php'; 

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['id'])) 
{
    // Return an error message if the user is not logged in
    echo json_encode(['error' => 'User not logged in']); 
    exit(); 
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['id']; 

try 
{
    // SQL query to fetch the recent check-ins for the logged-in user
    $recent_checkins_sql = 'SELECT Users.name, Events.event_name, CheckIn.checkin_time 
                            FROM CheckIn 
                            JOIN Users ON CheckIn.user_id = Users.user_id 
                            JOIN Events ON CheckIn.event_id = Events.event_id
                            WHERE CheckIn.user_id = :user_id
                            ORDER BY CheckIn.checkin_time DESC LIMIT 5';
    // Prepare the SQL statement                       
    $recent_checkins_stmt = $conn->prepare($recent_checkins_sql);
    // Bind the user ID parameter
    $recent_checkins_stmt->bindParam(':user_id', $user_id); 
    // Execute the SQL statement
    $recent_checkins_stmt->execute(); 
    // Fetch the results as an associative array
    $recent_checkins = $recent_checkins_stmt->fetchAll(PDO::FETCH_ASSOC); 

    // SQL query to fetch key analytics for the logged-in user
    $analytics_sql = 'SELECT COUNT(*) AS total_checkins, AVG(TIMEDIFF(CheckIn.checkin_time, Events.start_time)) AS avg_checkin_time 
                      FROM CheckIn 
                      JOIN Events ON CheckIn.event_id = Events.event_id
                      WHERE CheckIn.user_id = :user_id';
    $analytics_stmt = $conn->prepare($analytics_sql); 
    $analytics_stmt->bindParam(':user_id', $user_id); 
    $analytics_stmt->execute(); 
    $analytics = $analytics_stmt->fetch(PDO::FETCH_ASSOC); 

    // Return the recent check-ins and analytics as a JSON response
    echo json_encode(['recent_checkins' => $recent_checkins, 'analytics' => $analytics]);
} catch (PDOException $e) 
{
    // Log the database error
    error_log('Database error: ' . $e->getMessage()); 
    // Return an error message
    echo json_encode(['error' => 'Database error']); 
}
// Close the database connection
$conn = null; 
?>