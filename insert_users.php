<?php
require 'DB_Connection.php';

// Users data
$users = 
[
    ['name' => 'test', 'email' => 'john.doe@example.com', 'phone' => '1234567890', 'rfid_tag' => '677A563F', 'role' => 'admin', 'password' => '1'],
    ['name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'phone' => '0987654321', 'rfid_tag' => 'RFID654321', 'role' => 'user', 'password' => 'password456']
];

foreach ($users as $user) 
{
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO Users (name, email, phone, rfid_tag, role, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $user['name']);
    $stmt->bindParam(2, $user['email']);
    $stmt->bindParam(3, $user['phone']);
    $stmt->bindParam(4, $user['rfid_tag']);
    $stmt->bindParam(5, $user['role']);
    $stmt->bindParam(6, $hashed_password);
    $stmt->execute();
    $stmt->closeCursor();
}

// Insert example data into Events table
$events = [
    ['event_name' => 'Tech Conference', 'location' => 'Conference Hall A', 'start_time' => '2023-10-01 09:00:00', 'end_time' => '2023-10-01 17:00:00']
];

foreach ($events as $event) {
    $stmt = $conn->prepare("INSERT INTO Events (event_name, location, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->bindParam(1, $event['event_name']);
    $stmt->bindParam(2, $event['location']);
    $stmt->bindParam(3, $event['start_time']);
    $stmt->bindParam(4, $event['end_time']);
    $stmt->execute();
    $stmt->closeCursor();
}

// Insert example data into CheckIn table
$checkins = [
    ['user_id' => 1, 'event_id' => 1, 'checkin_time' => '2023-10-01 09:05:00', 'checkout_time' => '2023-10-01 17:00:00', 'status' => 'checked-in'],
    ['user_id' => 2, 'event_id' => 1, 'checkin_time' => '2023-10-01 09:10:00', 'checkout_time' => '2023-10-01 17:00:00', 'status' => 'checked-in']
];

foreach ($checkins as $checkin) {
    $stmt = $conn->prepare("INSERT INTO CheckIn (user_id, event_id, checkin_time, checkout_time, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $checkin['user_id']);
    $stmt->bindParam(2, $checkin['event_id']);
    $stmt->bindParam(3, $checkin['checkin_time']);
    $stmt->bindParam(4, $checkin['checkout_time']);
    $stmt->bindParam(5, $checkin['status']);
    $stmt->execute();
    $stmt->closeCursor();
}

// Insert example data into RFIDDevices table
$devices = [
    ['device_name' => 'Entrance Scanner', 'location' => 'Main Entrance', 'ip_address' => '192.168.1.10'],
    ['device_name' => 'Conference Room Scanner', 'location' => 'Conference Hall A', 'ip_address' => '192.168.1.11']
];

foreach ($devices as $device) {
    $stmt = $conn->prepare("INSERT INTO RFIDDevices (device_name, location, ip_address) VALUES (?, ?, ?)");
    $stmt->bindParam(1, $device['device_name']);
    $stmt->bindParam(2, $device['location']);
    $stmt->bindParam(3, $device['ip_address']);
    $stmt->execute();
    $stmt->closeCursor();
}

// Insert example data into AccessLogs table
$access_logs = [
    ['user_id' => 1, 'rfid_tag' => '677A563F', 'device_id' => 1, 'access_time' => '2023-10-01 09:00:00', 'status' => 'granted'],
    ['user_id' => 2, 'rfid_tag' => 'RFID654321', 'device_id' => 1, 'access_time' => '2023-10-01 09:05:00', 'status' => 'granted'],
    ['user_id' => 1, 'rfid_tag' => '677A563F', 'device_id' => 2, 'access_time' => '2023-10-01 09:05:00', 'status' => 'granted']
];

foreach ($access_logs as $log) {
    $stmt = $conn->prepare("INSERT INTO AccessLogs (user_id, rfid_tag, device_id, access_time, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $log['user_id']);
    $stmt->bindParam(2, $log['rfid_tag']);
    $stmt->bindParam(3, $log['device_id']);
    $stmt->bindParam(4, $log['access_time']);
    $stmt->bindParam(5, $log['status']);
    $stmt->execute();
    $stmt->closeCursor();
}

// Insert example data into Reports table
$reports = [
    ['event_id' => 1, 'total_checkins' => 2, 'avg_checkin_time' => '00:05:00']
];

foreach ($reports as $report) {
    $stmt = $conn->prepare("INSERT INTO Reports (event_id, total_checkins, avg_checkin_time) VALUES (?, ?, ?)");
    $stmt->bindParam(1, $report['event_id']);
    $stmt->bindParam(2, $report['total_checkins']);
    $stmt->bindParam(3, $report['avg_checkin_time']);
    $stmt->execute();
    $stmt->closeCursor();
}

$conn = null;
?>