<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

include 'DB_Connection.php';

$nodeMCU_IP = '192.168.2.186';

$lockFile = 'start_scan.lock';
if (file_exists($lockFile)) 
{
    logMessage('error', 'Another instance is already running');
    exit;
}
file_put_contents($lockFile, getmypid());

register_shutdown_function(function() use ($lockFile) 
{
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
});

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

function logMessage($status, $message) 
{
    echo json_encode(['status' => $status, 'message' => $message]);
}

$connectionStatus = checkNodeMCUConnection($nodeMCU_IP);
if ($connectionStatus === false || strpos($connectionStatus, 'RFID ready') === false) 
{
    logMessage('error', 'NodeMCU is not connected');
    exit;
}

$lastRFID = '';
$lastRFIDTime = 0;
$rfidCooldown = 30;

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $rfid = $_POST['rfid'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    if (!$rfid || !$user_id) 
    {
        logMessage('error', 'Invalid input');
        exit;
    }

    $startTime = time();
    while (true) 
    {
        if (time() - $startTime > 60) 
        {
            logMessage('error', 'Operation timed out');
            break;
        }

        $rfid = getRFIDFromNodeMCU($nodeMCU_IP);
        if ($rfid === false) 
        {
            logMessage('error', 'Failed to retrieve RFID');
            sleep(1);
            continue;
        }
        if ($rfid) 
        {
            $currentTime = time();
            if ($rfid == $lastRFID && ($currentTime - $lastRFIDTime) < $rfidCooldown) 
            {
                logMessage('error', "RFID $rfid ignored due to cooldown");
                sleep(1);
                continue;
            }

            $lastRFID = $rfid;
            $lastRFIDTime = $currentTime;

            logMessage('success', "Received RFID: $rfid");

            // Check if the RFID tag exists in the Users table
            $sql = "SELECT user_id, username FROM Users WHERE rfid_tag = :rfid_tag";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':rfid_tag', $rfid);
            if (!$stmt->execute()) 
            {
                logMessage('error', "Database error: " . implode(":", $stmt->errorInfo()));
                die();
            }

            if ($stmt->rowCount() > 0) 
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $user_id = $row['user_id'];
                $username = $row['username'];

                // Inform the admin that the RFID is already taken
                logMessage('error', "RFID $rfid is already taken by $username");
            } 
            else 
            {
                // Write the RFID to the text field for *RFID Tag
                logMessage('success', "RFID $rfid is available");
            }
            $lastRFID = ''; 
        } 
        else 
        {        
            logMessage('error', 'Failed to retrieve RFID');
        }
        sleep(1); 
    }
}

$conn = null;
if (file_exists($lockFile)) 
{
    unlink($lockFile);
}
?>
