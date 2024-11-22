<?php
session_start();
include 'DB_Connection.php';

// If no Admin is logged in, exit
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit();
}

// Check if dark mode is toggled
if (isset($_GET['toggle_dark_mode'])) {
    $_SESSION['dark_mode'] = !isset($_SESSION['dark_mode']) || !$_SESSION['dark_mode'];
}

// Prepare and execute the query
$query = "SELECT user_id, name, email, phone, rfid_tag, password, role FROM Guest";
$stmt = $conn->prepare($query);
$stmt->execute();

// Function to get the next free user ID from the Users table
function getNextFreeUserId($conn) {
    $stmt = $conn->query('SELECT user_id FROM Users ORDER BY user_id');
    $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $newUserId = 1;
    foreach ($existingIds as $id) {
        if ($id != $newUserId) {
            break;
        }
        $newUserId++;
    }
    return $newUserId;
}

// Function to transfer a guest to the Users table
function transferGuestToUsers($conn, $guestId) {
    // Get guest details
    $sql = 'SELECT * FROM Guest WHERE user_id = :user_id';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $guestId);
    $stmt->execute();
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($guest) {
        // Insert guest into Users table
        $sql = 'INSERT INTO Users (name, email, phone, rfid_tag, role, password) VALUES (:name, :email, :phone, :rfid_tag, :role, :password)';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $guest['name']);
        $stmt->bindParam(':email', $guest['email']);
        $stmt->bindParam(':phone', $guest['phone']);
        $stmt->bindParam(':rfid_tag', $guest['rfid_tag']);
        $stmt->bindParam(':role', $guest['role']);
        $stmt->bindParam(':password', $guest['password']);
        $stmt->execute();

        // Delete guest from Guest table
        $sql = 'DELETE FROM Guest WHERE user_id = :user_id';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $guestId);
        $stmt->execute();

        // Update user IDs to be sequential
        $sql = 'SET @count = 0';
        $conn->exec($sql);
        $sql = 'UPDATE Guest SET user_id = @count:= @count + 1';
        $conn->exec($sql);
        $sql = 'ALTER TABLE Guest AUTO_INCREMENT = 1';
        $conn->exec($sql);
    }
}

// Get the next free user ID
$nextFreeUserId = getNextFreeUserId($conn);

// Check if transfer request is made
if (isset($_GET['transfer_guest_id'])) {
    $guestId = $_GET['transfer_guest_id'];
    transferGuestToUsers($conn, $guestId);
    header('Location: /account-settings.html');
    exit();
}

// Check if there are any rows returned
if ($stmt->rowCount() > 0) {
    echo '<h1>Admin View</h1>'; // Add header
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($count % 3 == 0) {
            if ($count > 0) {
                echo '</div>'; // Close previous group
            }
            echo '<div class="guest-header-group">';
        }
        // Sanitize and display user ID and name
        $userId = htmlspecialchars($row['user_id']);
        $name = htmlspecialchars($row['name']);
        echo '<div class="guest-container">';
        echo '<div class="guest-header">';
        echo '<span class="toggle-button guest-id" onclick="toggleGuestFields(\'' . $userId . '\')">' . $name . '</span>';
        echo '</div>';
        echo '<div id="guest-fields-' . $userId . '" class="guest-fields section" style="display: none;">'; // Hide initially
        echo '<span id="guest-id-display-' . $userId . '" class="guest-id-display always-visible">Guest ID: ' . $userId . ' | User ID: ' . $nextFreeUserId . '</span>';
        echo '<form action="process_guest.php" method="post" onsubmit="return confirmSaveSettings(this)">'; // Update form action and add confirmation
        
        echo '<input type="hidden" name="user_id" value="' . $userId . '">';
        
        // Display and input for name
        echo '<label>*Name:</label>';
        echo '<input type="text" name="name" value="' . $name . '"><br>';
        
        // Display and input for email
        echo '<label>Email:</label>';
        echo '<input type="email" name="email" value="' . htmlspecialchars($row['email']) . '"><br>';
        
        // Display and input for phone
        echo '<label>Phone:</label>';
        echo '<input type="text" name="phone" value="' . htmlspecialchars($row['phone']) . '"><br>';
        
        // RFID input and button to assign RFID
        echo '<label>*RFID Tag:</label>';
        echo '<input type="text" id="rfid_' . $userId . '" name="rfid_tag" value="' . htmlspecialchars($row['rfid_tag']) . '">';
        echo '<button type="button" onclick="startRFIDScan(\'' . $userId . '\')" class="small-button">Scan RFID</button><br>';
        
        // Timer display for RFID scan
        echo '<div id="timer_' . $userId . '" style="display: none;"></div><br>';
        
        // Display and input for password
        echo '<label>*Password:</label>';
        echo '<input type="password" name="password" value="' . htmlspecialchars($row['password']) . '"><br>';
        
        // Display and input for role
        echo '<label>*Role:</label><br>';
        echo '<div class="role-options">';
        echo '<label><input type="radio" name="role" value="user" checked> User</label>'; // Always select "User" by default
        echo '<label><input type="radio" name="role" value="admin"' . ($row['role'] === 'admin' ? ' checked' : '') . '> Admin</label>';
        echo '</div><br>';
        
        // Submit button for each guest
        echo '<button type="submit" name="save_guest">Save Settings</button>';
        // Add delete button for each guest
        echo '<button type="submit" name="delete_guest" class="delete-button" onclick="return confirmDeleteGuest(' . $userId . ')">Delete Guest</button>';
        echo '</form>';
        echo '<p style="font-size: smaller;">* pflichtfeld</p>';
        echo '</div>';
        echo '<hr>';
        echo '</div>';
        $count++;
    }
    echo '</div>'; // Close last group
} else {
    echo "No guests found.";
}

$conn = null;
?>

<!-- Link to the external JavaScript file -->
<script src="rfid_scan.js"></script>
<script src="admin_view.js"></script> <!-- Link to the new JavaScript file -->
<link rel="stylesheet" href="styles.css"> <!-- Link to the CSS file -->
<link rel="stylesheet" href="admin_view.css"> <!-- Link to the new CSS file -->

<script>
    function confirmDeleteGuest(userId) {
        if (confirm('Are you sure you want to delete this guest?')) {
            fetch('delete_guest.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'user_id=' + userId
            })
            .then(response => response.text())
            .then(data => {
                alert('Guest deleted successfully!');
                window.location.href = 'account-settings.html';
            })
            .catch(error => {
                console.error('Error deleting guest:', error);
                alert('Error deleting guest.');
            });
        }
    }

    function confirmSaveSettings(form) {
        return confirm('Are you sure you want to save these settings?');
    }
</script>