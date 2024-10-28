<?php
// Inlucde the database connection from the file
include 'DB_Connection.php';

// Retrive POST data from the form submission
$user_ids = $_POST['user_id'];
$names = $_POST['name'];
$emails = $_POST['email'];
$phones = $_POST['phone'];
$rfid_tags = $_POST['rfid_tag'];
$roles = $_POST['role'];
$passwords = $_POST['password'];

// Loop through each data set from Users table and update it
for ($i = 0; $i < count($user_ids); $i++) 
{
    $user_id = $user_ids[$i];
    $name = $names[$i];
    $email = $emails[$i];
    $phone = $phones[$i];
    $rfid_tag = $rfid_tags[$i];
    $role = $roles[$i];
    $password = $passwords[$i];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data into Users table
    $insertQuery = $conn->prepare("INSERT INTO Users (name, email, phone, rfid_tag, role, password) VALUES (?, ?, ?, ?, ?, ?)");
    $insertQuery->bindValue(1, $name);
    $insertQuery->bindValue(2, $email);
    $insertQuery->bindValue(3, $phone);
    $insertQuery->bindValue(4, $rfid_tag);
    $insertQuery->bindValue(5, $role);
    $insertQuery->bindValue(6, $hashed_password);
    $insertQuery->execute();

    // Prepare the SQL query to delete the guest from the Guest table
    $deleteQuery = $conn->prepare("DELETE FROM Guest WHERE user_id = ?");
    $deleteQuery->bindValue(1, $user_id, PDO::PARAM_INT);
    $deleteQuery->execute();

    // Redirect to the account settings page
    header('location: http://localhost/account-settings.html');

    // Close prepared statements
    $insertQuery = null;
    $deleteQuery = null;
}

// Close the database connection
$conn = null;

// output a seccess message
echo "Data processed successfully.";
?>