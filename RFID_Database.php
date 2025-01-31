<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); 

include 'DB_Connection.php';
include 'time.php'; 

$nodeMCU_IP = '192.168.2.186';
$lockFile = 'start_scan.lock';

if (file_exists($lockFile)) {
    echo json_encode(['status' => 'info', 'message' => 'start_scan.php is running, RFID_Database.php is paused.']);
    exit;
}

function checkNodeMCUConnection($ip) 
{
    $url = "http://$ip/status";
    $response = @file_get_contents($url);
    if ($response === FALSE) 
    {
        return false;
    }
    return $response;
}

function getRFIDFromNodeMCU($ip) 
{
    $url = "http://$ip/getRFID";
    $response = @file_get_contents($url);
    if ($response === FALSE) 
    {
        return false;
    }
    return $response;
}

$connectionStatus = checkNodeMCUConnection($nodeMCU_IP);
if ($connectionStatus === false || strpos($connectionStatus, 'RFID ready') === false) 
{
    echo json_encode(['status' => 'error', 'message' => 'NodeMCU is not connected']);
    exit;
}

$lastRFID = '';
$lastRFIDTime = 0;
$rfidCooldown = 30; 

while (true) 
{
    if (file_exists($lockFile)) 
    {
        echo json_encode(['status' => 'info', 'message' => 'start_scan.php is running, RFID_Database.php is paused.']);
        sleep(5);
        continue;
    }

    $rfid = getRFIDFromNodeMCU($nodeMCU_IP);
    if ($rfid === false) 
    {
        echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve RFID']);
        sleep(1);
        continue;
    }
    if ($rfid) 
    {
        $currentTime = time();
        if ($rfid == $lastRFID && ($currentTime - $lastRFIDTime) < $rfidCooldown) 
        {
            echo json_encode(['status' => 'error', 'message' => "RFID $rfid ignored due to cooldown"]);
            sleep(1);
            continue;
        }

        $lastRFID = $rfid;
        $lastRFIDTime = $currentTime;

        echo json_encode(['status' => 'success', 'message' => "Received RFID: $rfid"]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rfid = $_POST['rfid'];

            // Check if the RFID tag exists in the Users table
            $sql = "SELECT user_id FROM Users WHERE rfid_tag = :rfid_tag";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':rfid_tag', $rfid);
            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'message' => "Database error: " . implode(":", $stmt->errorInfo())]);
                exit;
            }

            if ($stmt->rowCount() > 0) 
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_id = $row['user_id'];

                // Check if the user is already checked in
                $sql = "SELECT checkin_id FROM CheckIn WHERE user_id = :user_id AND checkout_time IS NULL";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $user_id);
                if (!$stmt->execute()) 
                {
                    echo json_encode(['status' => 'error', 'message' => "Database error: " . implode(":", $stmt->errorInfo())]);
                    exit;
                }

                if ($stmt->rowCount() > 0) 
                {
                    // User is already checked in, log the checkout time
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $checkin_id = $row['checkin_id'];
                    $checkout_time = date("Y-m-d H:i:s"); 
                    $sql = "UPDATE CheckIn SET checkout_time = :checkout_time WHERE checkin_id = :checkin_id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':checkout_time', $checkout_time);
                    $stmt->bindParam(':checkin_id', $checkin_id);
                    if (!$stmt->execute()) {
                        echo json_encode(['status' => 'error', 'message' => "Database error: " . implode(":", $stmt->errorInfo())]);
                        exit;
                    }
                    echo json_encode(['status' => 'success', 'message' => "Checked out successfully at $checkout_time"]);
                } 
                else 
                {
                    // User is not checked in, log the check-in time
                    $checkin_time = date("Y-m-d H:i:s"); 
                    $sql = "INSERT INTO CheckIn (user_id, event_id, status, checkin_time) VALUES (:user_id, :event_id, :status, :checkin_time)";
                    $stmt = $conn->prepare($sql);
                    $event_id = 1; 
                    $status = 'checked_in';
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':event_id', $event_id);
                    $stmt->bindParam(':status', $status);
                    $stmt->bindParam(':checkin_time', $checkin_time);
                    if (!$stmt->execute()) 
                    {
                        echo json_encode(['status' => 'error', 'message' => "Database error: " . implode(":", $stmt->errorInfo())]);
                        exit;
                    }
                    echo json_encode(['status' => 'success', 'message' => "Checked in successfully at $checkin_time"]);
                }
            } 
            else 
            {
                echo json_encode(['status' => 'error', 'message' => 'Access denied']);
            }
            $lastRFID = ''; // Reset RFID after processing
        } 
        else 
        {        
            echo json_encode(['status' => 'error', 'message' => 'Failed to retrieve RFID']);
        }
        sleep(1); // Wait for 1 second before the next request
    }
}

$conn = null;
?>