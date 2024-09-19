<?php
include 'DB_Connection.php';

// Fetch recent check-ins
$recent_checkins_sql = "SELECT Users.name, Events.event_name, CheckIn.checkin_time 
                        FROM CheckIn 
                        JOIN Users ON CheckIn.user_id = Users.user_id 
                        JOIN Events ON CheckIn.event_id = Events.event_id 
                        ORDER BY CheckIn.checkin_time DESC LIMIT 5";
$recent_checkins_result = $conn->query($recent_checkins_sql);

// Fetch key analytics
$analytics_sql = "SELECT COUNT(*) AS total_checkins, AVG(TIMEDIFF(CheckIn.checkin_time, Events.start_time)) AS avg_checkin_time 
                  FROM CheckIn 
                  JOIN Events ON CheckIn.event_id = Events.event_id";
$analytics_result = $conn->query($analytics_sql);

$recent_checkins = [];
if ($recent_checkins_result->num_rows > 0) 
{
    while($row = $recent_checkins_result->fetch_assoc()) 
    {
        $recent_checkins[] = $row;
    }
}

$analytics = $analytics_result->fetch_assoc();

$conn->close();

echo json_encode(['recent_checkins' => $recent_checkins, 'analytics' => $analytics]);
?>
