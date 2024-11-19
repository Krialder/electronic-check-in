<?php
session_start();

include 'DB_Connection.php';

// If no Admin is logged in, exit
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') 
{
    exit('Unauthorized access');
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Verify CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo 'Invalid CSRF token';
        exit();
    }
    // Sanitize form data
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $rfid_tag = filter_var($_POST['rfid_tag'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
    $role = filter_var($_POST['role'], FILTER_SANITIZE_STRING);

    // Regenerate CSRF token after successful form submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        echo '<form action="admin_view.php" method="post">';
        echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($userId) . '">';
        echo '<input type="hidden" name="csrf_token" value="' . $csrf_token . '">';
        
        // Display and input for name
        echo '<label>*Name:</label>';
        echo '<input type="text" name="name" value="' . htmlspecialchars($row['name']) . '"><br>';
        
        // Display and input for email
        $email = filter_var($row['email'], FILTER_VALIDATE_EMAIL);
        echo '<label>Email:</label>';
        echo '<input type="email" name="email" value="' . htmlspecialchars($email) . '"><br>';
        
        // Display and input for phone
        $phone = filter_var($row['phone'], FILTER_SANITIZE_STRING);
        echo '<label>Phone:</label>';
        echo '<input type="text" name="phone" value="' . htmlspecialchars($phone) . '"><br>';
        
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
<script src="admin_view.js"></script>
<link rel="stylesheet" href="admin_view.css">
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

.guest-container {
    margin-bottom: 20px;
}

#timer {
    display: none;
}
</style>