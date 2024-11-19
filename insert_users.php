<?php
session_start();
include 'DB_Connection.php';

// Check CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('Invalid CSRF token');
    }
}

try {
    // Retrieve and sanitize form data
    $username = filter_var($_POST['username'] ?? null, FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? null, FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? null;

    // Validate form data
    if (empty($username) || empty($email) || empty($password)) {
        throw new Exception('Please fill in all required fields');
    }

    // Check if username or email already exists
    $sql = 'SELECT COUNT(*) FROM Users WHERE username = :username OR email = :email';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        throw new Exception('Username or email already exists');
    }

    // Hash the password
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert user data into the database
    $sql = 'INSERT INTO Users (username, email, password) VALUES (:username, :email, :password)';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hash);
    $stmt->execute();

    // Redirect to the specified page with success message
    header('Location: /login.html?success=1');
    exit();
} catch (Exception $e) {
    // Log the error and redirect with an error message
    error_log('Error: ' . $e->getMessage());
    header('Location: /register.html?error=' . urlencode($e->getMessage()));
    exit();
}
?>