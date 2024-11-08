<?php
session_start();

include 'DB_Connection.php';

// If no Admin is logged in, exit
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') 
{
    exit();
}

// Prepare and execute the query
$query = "SELECT user_id, name, email, phone, rfid_tag, password, role FROM Guest";
$stmt = $conn->prepare($query);
$stmt->execute();

// Check if there are any rows returned
if ($stmt->rowCount() > 0) 
{
    echo '<h1>Admin View</h1>'; // Add header
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
    {
        // Sanitize and display user ID
        $userId = htmlspecialchars($row['user_id']);
        echo '<div class="guest-container">';
        echo '<h2><span class="toggle-button" onclick="toggleGuestFields(\'' . $userId . '\')">Guest ID: ' . $userId . '</span></h2>';
        echo '<div id="guest-fields-' . $userId . '" style="display: none;">';
        echo '<form action="process_guest.php" method="post">';
        echo '<input type="hidden" name="user_id" value="' . $userId . '">';
        
        // Display and input for name
        echo '<label>*Name:</label>';
        echo '<input type="text" name="name" value="' . htmlspecialchars($row['name']) . '"><br>';
        
        // Display and input for email
        echo '<label>Email:</label>';
        echo '<input type="email" name="email" value="' . htmlspecialchars($row['email']) . '"><br>';
        
        // Display and input for phone
        echo '<label>Phone:</label>';
        echo '<input type="text" name="phone" value="' . htmlspecialchars($row['phone']) . '"><br>';
        
        // RFID input and button to assign RFID
        echo '<label>*RFID Tag:</label>';
        echo '<input type="text" id="rfid_' . $userId . '" name="rfid_tag" value="' . htmlspecialchars($row['rfid_tag']) . '">';
        echo '<button type="button" onclick="startRFIDScan(\'' . $userId . '\')">Scan RFID</button><br>';
        
        // Timer display for RFID scan
        echo '<div id="timer_' . $userId . '" style="display: none;"></div><br>';

        // Display and input for password
        echo '<label>*Password:</label>';
        echo '<input type="password" name="password" value="' . htmlspecialchars($row['password']) . '"><br>';
        
        // Display and input for role
        echo '<label>*Role:</label><br>';
        echo '<div class="role-options">';
        echo '<label><input type="radio" name="role" value="user"' . ($row['role'] === 'user' ? ' checked' : '') . '> User</label>'; // Ensure "User" is selected by default
        echo '<label><input type="radio" name="role" value="admin"' . ($row['role'] === 'admin' ? ' checked' : '') . '> Admin</label>';
        echo '</div><br>';
        
        // Submit button for each guest
        echo '<button type="submit" name="save_guest">Save Settings</button>';
        echo '</form>';
        echo '<p style="font-size: smaller;">* pflichtfeld</p>';
        echo '</div>';
        echo '<hr>';
        echo '</div>';
    }
} 
else 
{
    echo "No guests found.";
}

$conn = null;
?>

<!-- Link to the external JavaScript file -->
<script src="rfid_scan.js"></script>
<script>
function toggleGuestFields(userId) 
{
    var guestFields = document.getElementById('guest-fields-' + userId);
    if (guestFields.style.display === 'none') 
    {
        guestFields.style.display = 'block';
    } 
    else 
    {
        guestFields.style.display = 'none';
    }
}

function startRFIDScan(userId) 
{
    var timer = document.getElementById('timer_' + userId);
    timer.style.display = 'block';
    timer.value = 'Scanning...';

    console.log('Sending request to NodeMCU to start scanning'); // Debugging statement

    // Send a request to the NodeMCU to start scanning
    fetch('http://localhost/start_scan')
        .then(response => response.text())
        .then(data => {
            console.log('Received RFID tag from NodeMCU:', data); // Debugging statement

            // Update the RFID input field with the scanned RFID tag
            document.getElementById('rfid_' + userId).value = data;
            timer.style.display = 'none';

            // Send the scanned RFID tag to the RFID_Database.php endpoint
            fetch('RFID_Database.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'rfid=' + encodeURIComponent(data)
            })
            .then(response => response.text())
            .then(result => {
                console.log('Server Response:', result); // Debugging statement
            })
            .catch(error => {
                console.error('Error sending RFID to database:', error); // Debugging statement
                timer.value = 'Scan failed';
            });
        })
        .catch(error => {
            console.error('Error sending request to NodeMCU:', error); // Debugging statement
            timer.value = 'Scan failed';
        });
}
</script>
<style>
.toggle-button 
{
    cursor: pointer;
    color: blue;
    text-decoration: underline;
}

.role-options {
    display: flex;
    align-items: center;
    margin-left: 
}

.role-options label 
{
    display: inline-block;
    margin-right: 10px;
    font-size: smaller; 
}

button[type="submit"] {
    font-size: 14px; 
}
</style>