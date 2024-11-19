<?php
session_start();
include 'DB_Connection.php';

// Generate CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('Invalid CSRF token');
    }

    // Retrieve and sanitize form data
    $username = filter_var($_POST['username'] ?? null, FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;
    $rfid = filter_var($_POST['rfid'] ?? null, FILTER_SANITIZE_STRING);

    // Validate form data
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($rfid)) {
        $error_msg = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = 'Invalid email format';
    } elseif ($password !== $confirm_password) {
        $error_msg = 'Passwords do not match';
    } else {
        // Check if username, email, or RFID already exists
        $sql = 'SELECT COUNT(*) FROM Users WHERE username = :username OR email = :email OR rfid = :rfid';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $error_msg = 'Username, email, or RFID already exists';
        } else {
            // Hash the password
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user data into the database
            $sql = 'INSERT INTO Users (username, email, password, rfid) VALUES (:username, :email, :password, :rfid)';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hash);
            $stmt->bindParam(':rfid', $rfid);
            $stmt->execute();

            // Redirect to the specified page with success message
            header('Location: /login.html?success=1');
            exit();
        }
    }

    // Redirect back to register.html with error message
    header('Location: /register.html?error=' . urlencode($error_msg));
    exit();
}
?>