<?php
session_start();

include 'DB_Connection.php';

//If no Admin is logged in, exit
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') 
{
    exit();
}

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->execute();

// Check if there are any rows returned
if ($stmt->rowCount() > 0) 
{
    // Start the form for processing guest data
    echo '<form action="process_guest.php" method="post">';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
    {
        // Sanitize and display user ID
        $userId = htmlspecialchars($row['user_id']);
        echo '<h2>Guest ID: ' . $userId . '</h2>';
        echo '<input type="hidden" name="user_id[]" value="' . $userId . '">';
        
        // Display and input for name
        echo '<label>Name:</label>';
        echo '<input type="text" name="name[]" value="' . htmlspecialchars($row['name']) . '"><br>';
        
        // Display and input for email
        echo '<label>Email:</label>';
        echo '<input type="email" name="email[]" value="' . htmlspecialchars($row['email']) . '"><br>';
        
        // Display and input for phone
        echo '<label>Phone:</label>';
        echo '<input type="text" name="phone[]" value="' . htmlspecialchars($row['phone']) . '"><br>';
        
        // RFID input and button to assign RFID
        echo '<label>RFID Tag:</label>';
        echo '<input type="text" id="rfid_' . $userId . '" name="rfid_tag[]" value="' . htmlspecialchars($row['rfid_tag']) . '">';
        echo '<button type="button" onclick="startRFIDScan(\'' . $userId . '\')">Scan RFID</button><br>';
        
        // Timer display for RFID scan
        echo '<div id="timer_' . $userId . '"></div>';

        // Display and input for role
        echo '<label>Role:</label>';
        echo '<input type="text" name="role[]" value="' . htmlspecialchars($row['role']) . '"><br>';
        
        // Display and input for password
        echo '<label>Password:</label>';
        echo '<input type="password" name="password[]" value="' . htmlspecialchars($row['password']) . '"><br>';
        
        // Separator for each guest
        echo '<hr>';
    }
    // Submit button for the form
    echo '<input type="submit" value="Submit">';
    echo '</form>';
} 
else 
{
    echo "No guests found.";
}

$conn = null;
?>

<script>
// Declare variables for RFID timeout and countdown interval
let rfidTimeout;
let countdownInterval;
let timeLeft;

// Function to start RFID scan for a specific user
function startRFIDScan(userId) 
{
    // Clear any existing timeouts and intervals
    clearTimeout(rfidTimeout);
    clearInterval(countdownInterval);
    alert("Please scan RFID within 1 minute.");

    // Set the countdown time to 60 seconds
    timeLeft = 60;
    const timerDisplay = document.getElementById('timer_' + userId);

    // Check if the timer display element exists
    if (!timerDisplay) 
    {
        console.error('Timer display element not found for user ID:', userId);
        return;
    }

    // Update the timer every second
    countdownInterval = setInterval(() =>
    {
        if (timeLeft <= 0)
        {
            // Clear interval and update timer display when time is up
            clearInterval(countdownInterval);
            timerDisplay.innerHTML = "RFID scan timed out.";
        }
        else
        {
            // Update timer display and decrement time left
            timerDisplay.innerHTML = 'Time left: ' + timeLeft + ' seconds';
            console.log('Time left:', timeLeft); // Debug log
            timeLeft--;
        }
    }, 1000);

    // Get the RFID input element
    const rfidInput = document.getElementById('rfid_' + userId);
    const onRFIDScan = function(event) 
    {
        if (!rfidInput) return;

        // Capture the RFID code from input (-> Enter key might finalize input from scanner)
        if (event.key === 'Enter') 
        {
            // Trim and assign RFID value, then remove event listener and clear interval
            rfidInput.value = rfidInput.value.trim();
            alert("RFID scanned and assigned to user.");
            document.removeEventListener('keydown', onRFIDScan);
            clearInterval(countdownInterval);
            timerDisplay.innerHTML = "RFID scan completed.";
        } 
        else 
        {
            // Append the key to the RFID input value
            rfidInput.value += event.key;
        }
    };

    // Add event listener for RFID scan
    document.addEventListener('keydown', onRFIDScan);

    // Timeout after 1 minute if no RFID is scanned
    rfidTimeout = setTimeout(() => 
    {
        // Alert timeout, remove event listener, and clear interval
        alert("RFID scan timed out.");
        document.removeEventListener('keydown', onRFIDScan);
        clearInterval(countdownInterval);
        timerDisplay.innerHTML = "RFID scan timed out.";
    }, 60000);
}
</script>