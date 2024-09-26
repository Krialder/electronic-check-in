<?php
include 'DB_Connection.php';

try {
    // Fetch recent check-ins
    $recent_checkins_sql = "SELECT Users.name, Events.event_name, CheckIn.checkin_time 
                            FROM CheckIn 
                            JOIN Users ON CheckIn.user_id = Users.user_id 
                            JOIN Events ON CheckIn.event_id = Events.event_id 
                            ORDER BY CheckIn.checkin_time DESC LIMIT 5";
    $recent_checkins_stmt = $conn->prepare($recent_checkins_sql);
    $recent_checkins_stmt->execute();
    $recent_checkins = $recent_checkins_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch key analytics
    $analytics_sql = "SELECT COUNT(*) AS total_checkins, AVG(TIMEDIFF(CheckIn.checkin_time, Events.start_time)) AS avg_checkin_time 
                      FROM CheckIn 
                      JOIN Events ON CheckIn.event_id = Events.event_id";
    $analytics_stmt = $conn->prepare($analytics_sql);
    $analytics_stmt->execute();
    $analytics = $analytics_stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['recent_checkins' => $recent_checkins, 'analytics' => $analytics]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}

$conn = null;
?>