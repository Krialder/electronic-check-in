<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Debugging statement
error_log('register.php beginning');

// Include the database connection from the file
include 'DB_Connection.php';

// Start session for CSRF token
session_start();

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Define variables and initialize with empty values
$forname = $surname = $name = '';
$forname_err = $surname_err = $password_err = $name_err = '';

if (isset($_POST['register'])) {
    // CSRF token verification
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('Invalid CSRF token');
    }

    // Retrieve and sanitize form data
    $forname = filter_var($_POST['forname'], FILTER_SANITIZE_STRING);
    $surname = filter_var($_POST['surname'], FILTER_SANITIZE_STRING);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $role = 'guest';
    $user_rfid_tag = NULL;
    $phone = NULL;

    // Check if username already exists in Guest table
    $sql = "SELECT COUNT(*) FROM Guest WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name]);
    $guest_count = $stmt->fetchColumn();

    // Check if username already exists in Users table
    $sql = "SELECT COUNT(*) FROM Users WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name]);
    $user_count = $stmt->fetchColumn();

    if ($guest_count > 0 || $user_count > 0) {
        $name_err = 'Username is not available';
    } else {
        // Check if passwords match
        if ($password !== $password2) {
            $password_err = 'Passwords do not match';
        } elseif (empty($password)) {
            $password_err = 'Please enter a password';
        } elseif (!ctype_alpha($forname)) {
            $forname_err = 'First name can only contain letters';
        } elseif (empty($forname)) {
            $forname_err = 'Please enter a first name';
        } elseif (!ctype_alpha($surname)) {
            $surname_err = 'Last name can only contain letters';
        } elseif (empty($surname)) {
            $surname_err = 'Please enter a last name';
        } else {
            // Check for existing user IDs and find the first non-existing number
            $sql = "SELECT user_id FROM Guest ORDER BY user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $existing_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            $new_user_id = 1;
            foreach ($existing_ids as $id) {
                if ($id != $new_user_id) {
                    break;
                }
                $new_user_id++;
            }

            // Hash the password before storing it in the database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the SQL statement to insert the new guest into the database
            $sql = "INSERT INTO Guest (user_id, name, email, phone, rfid_tag, role, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // Bind the parameters to the SQL query
            $stmt->bindValue(1, $new_user_id);
            $stmt->bindValue(2, $name);
            $stmt->bindValue(3, $email);
            $stmt->bindValue(4, $phone);
            $stmt->bindValue(5, $user_rfid_tag);
            $stmt->bindValue(6, $role);
            $stmt->bindValue(7, $hashed_password);

            // Execute the statement and check if the registration was successful
            if ($stmt->execute()) {
                echo 'Registration successful';
                header('location: testy.html');
                exit;
            } else {
                // Output error message if the registration failed
                echo 'Error: ' . htmlspecialchars($stmt->errorInfo()[2]);
            }
            $stmt = null;
        }
    }
}

$conn = null;
?>