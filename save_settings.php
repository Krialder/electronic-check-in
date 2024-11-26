
<?php
session_start();
include 'DB_Connection.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    exit('Unauthorized access');
}

// Get the user ID from the session
$user_id = $_SESSION['id'];

// Get the form data
$email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
$sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
$dark_mode = isset($_POST['theme_toggle']) ? 1 : 0;

// Update the user's settings in the database
$sql = 'UPDATE Users SET email_notifications = :email_notifications, sms_notifications = :sms_notifications, dark_mode = :dark_mode WHERE user_id = :user_id';
$stmt = $conn->prepare($sql);
$stmt->bindParam(':email_notifications', $email_notifications);
$stmt->bindParam(':sms_notifications', $sms_notifications);
$stmt->bindParam(':dark_mode', $dark_mode);
$stmt->bindParam(':user_id', $user_id);

if ($stmt->execute()) {
    // Redirect back to account-settings.html with a success message
    header('Location: /account-settings.html?success=1');
} else {
    // Redirect back to account-settings.html with an error message
    header('Location: /account-settings.html?error=1');
}
exit();
?>