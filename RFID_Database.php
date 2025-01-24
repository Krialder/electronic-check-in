<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'DB_Connection.php';
include 'time.php'; 

function checkNodeMCUConnection($nodeMCU_IP) 
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$nodeMCU_IP/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    echo "NodeMCU response: $response"; 
    if (strpos($response, 'RFID ready') !== false) 
    {
        preg_match('/Last RFID: (\w+)/', $response, $matches);
        return $matches[1] ?? false;
    }
    return false;
}

function getRFIDFromNodeMCU($nodeMCU_IP) 
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$nodeMCU_IP/getRFID");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}

$nodeMCU_IP = '192.168.2.186';

$lastRFID = checkNodeMCUConnection($nodeMCU_IP);
if (!$lastRFID) 
{
    die('NodeMCU is not connected');
}

while (true) 
{
    $rfid = getRFIDFromNodeMCU($nodeMCU_IP) ?: $lastRFID;
    if ($rfid) 
    {
        echo "Received RFID: $rfid";

        // Check if the RFID tag exists in the Users table
        $sql = "SELECT user_id FROM Users WHERE rfid_tag = :rfid_tag";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':rfid_tag', $rfid);
        if (!$stmt->execute()) 
        {
            echo "Database error: " . implode(":", $stmt->errorInfo());
            die();
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
                echo "Database error: " . implode(":", $stmt->errorInfo());
                die();
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
                    echo "Database error: " . implode(":", $stmt->errorInfo());
                    die();
                }
                echo "Checked out successfully at $checkout_time";
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
                    echo "Database error: " . implode(":", $stmt->errorInfo());
                    die();
                }
                echo "Checked in successfully at $checkin_time";
            }
        } 
        else 
        {
            echo "Access denied";
        }
    } 
    else 
    {
        echo "Failed to retrieve RFID";
    }
    sleep(1); // Wait for 1 second before the next request
}

$conn = null;
?>