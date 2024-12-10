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

// Calculate the next User ID
$nextUserIdQuery = "SELECT MAX(user_id) + 1 AS next_user_id FROM Users";
$nextUserIdStmt = $conn->prepare($nextUserIdQuery);
$nextUserIdStmt->execute();
$nextUserId = $nextUserIdStmt->fetch(PDO::FETCH_ASSOC)['next_user_id'] ?? 1;

// Check if there are any rows returned
if ($stmt->rowCount() > 0) 
{
    echo '<h1>Admin View</h1>'; // Add header
    echo '<div class="guest-container-wrapper">';
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
    {
        // Sanitize and display user ID and name
        $userId = htmlspecialchars($row['user_id']);
        $userName = htmlspecialchars($row['name']);
        echo '<div class="guest-container" id="guest-container-' . $userId . '">';
        echo '<h2><span class="toggle-button" onclick="toggleGuestFields(\'' . $userId . '\')">' . $userName . '</span></h2>'; // Use only username as toggle
        echo '<div id="guest-fields-' . $userId . '" style="display: none;">';
        echo '<form action="process_guest.php" method="post" onsubmit="updateNextUserId(event)">';
        echo '<input type="hidden" name="user_id" value="' . $userId . '">';

        // Display Guest ID and Next User ID
        echo '<label>Guest ID: ' . $userId . ' | Next User ID: <span id="next-user-id-' . $userId . '">' . $nextUserId . '</span></label>';
        
        // Display and input for name
        echo '<label>*Name:</label>';
        echo '<input type="text" name="name" value="' . $userName . '">';
        
        // Display and input for email
        echo '<label>Email:</label>';
        echo '<input type="email" name="email" value="' . htmlspecialchars($row['email']) . '">';
        
        // Display and input for phone
        echo '<label>Phone:</label>';
        echo '<input type="text" name="phone" value="' . htmlspecialchars($row['phone']) . '">';
        
        // RFID input and button to assign RFID
        echo '<label>*RFID Tag:</label>';
        echo '<input type="text" id="rfid_' . $userId . '" name="rfid_tag" value="' . htmlspecialchars($row['rfid_tag']) . '">';
        echo '<button type="button" onclick="startRFIDScan(\'' . $userId . '\')">Scan RFID</button>';
        
        // Delete button
        echo '<button type="button" onclick="confirmDeleteGuest(\'' . $userId . '\')">Delete Guest</button>';
        
        // Timer display for RFID scan
        echo '<div id="timer_' . $userId . '" style="display: none;"></div>';

        // Display and input for password
        echo '<label>*Password:</label>';
        echo '<input type="password" name="password" value="' . htmlspecialchars($row['password']) . '">';

        // Display and input for role
        echo '<label>*Role:</label>';
        echo '<div class="role-options">';
        echo '<label><input type="radio" name="role" value="user" checked> User</label>';
        echo '<label><input type="radio" name="role" value="admin"' . ($row['role'] === 'admin' ? ' checked' : '') . '> Admin</label>';
        echo '</div>';
        
        // Submit button for each guest
        echo '<button type="submit" name="save_guest">Save Settings</button>';
        echo '</form>';
        echo '<p>* pflichtfeld</p>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
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
    var guestContainer = document.getElementById('guest-container-' + userId);
    var allGuestFields = document.querySelectorAll('[id^="guest-fields-"]');
    var allGuestContainers = document.querySelectorAll('.guest-container');
    
    allGuestFields.forEach(function(field) 
    {
        if (field.id !== 'guest-fields-' + userId) 
        {
            field.style.display = 'none';
        }
    });

    allGuestContainers.forEach(function(container) 
    {
        if (container.id !== 'guest-container-' + userId) 
        {
            container.classList.remove('toggled');
        }
    });

    if (guestFields.style.display === 'none') 
    {
        guestFields.style.display = 'block';
        guestContainer.classList.add('toggled');
    } 
    else 
    {
        guestFields.style.display = 'none';
        guestContainer.classList.remove('toggled');
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
        .then(response => response.json())
        .then(result => 
        {
            console.log('Received RFID tag from NodeMCU:', result); // Debugging statement

            if (result.status === 'success') 
            {
                // Update the RFID input field with the scanned RFID tag
                document.getElementById('rfid_' + userId).value = result.rfid_tag;
                timer.style.display = 'none';
            } 
            else 
            {
                timer.value = result.message;
            }
        })
        .catch(error => 
        {
            console.error('Error sending request to NodeMCU:', error); // Debugging statement
            timer.value = 'Scan failed';
        });
}

function updateNextUserId(event) 
{
    event.preventDefault();
    fetch('update_next_user_id.php')
        .then(response => response.json())
        .then(data => 
        {
            document.getElementById('next-user-id').textContent = data.next_user_id;
            event.target.submit();
        })
        .catch(error => console.error('Error updating next user ID:', error));
}

function confirmDeleteGuest(userId) 
{
    console.log('Attempting to delete guest with userId:', userId); // Debugging statement
    if (confirm('Are you sure you want to delete this guest?')) 
    {
        fetch('delete_guest.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Response from delete_guest.php:', data); // Debugging statement
            if (data.success) {
                document.getElementById('guest-container-' + userId).remove();
                alert('Guest deleted successfully.');
                updateNextUserId();
            } else {
                alert('Error deleting guest: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting guest:', error);
            alert('Error deleting guest: ' + error.message);
        });
    }
}

function updateNextUserId() 
{
    console.log('Updating next user ID'); // Debugging statement
    fetch('update_next_user_id.php')
        .then(response => response.json())
        .then(data => {
            console.log('Response from update_next_user_id.php:', data); // Debugging statement
            document.getElementById('next-user-id').textContent = data.next_user_id;
        })
        .catch(error => console.error('Error updating next user ID:', error));
}
</script>
<style>
.toggle-button 
{
    cursor: pointer;
    color: blue;
    text-decoration: underline;
}

.role-options 
{
    display: flex;
    align-items: center;
    margin-left: 
}

.role-options label 
{
    display: inline-block;
    margin-right: 10px;    font-size: smaller; 
}
button[type="submit"] 
    {    
        font-size: 14px; 
    }
</style>