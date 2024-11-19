<?php

session_start();

error_reporting(E_ALL); 

ini_set('display_errors', 1); 

error_log('registration.php beginning'); 

include 'DB_Connection.php'; 
// Check if the user role is admin
$user_role = $_SESSION['role'] === 'admin'; 

// Exit if the user is not an admin
if (!$user_role) 
{
    echo 'Access denied. Only admins can register users.';
    exit(); 
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    exit('Invalid CSRF token');
}

if (isset($_POST['register']))
{
    // Retrieve and sanitize form data
    $forname = filter_var($_POST['Vorname'], FILTER_SANITIZE_STRING);
    $surname = filter_var($_POST['Nachname'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Validate passwords
    if ($password !== $password2)
    {  
        $password_err = 'Passwords do not match'; 
    }
    else if (empty($password))
    {
        $password_err = 'Please enter a password'; 
    }
    else if (empty($email))
    {
        $email_err = 'Please enter an email address'; 
    }
    else if (empty($forname))
    {
        $forname_err = 'Please enter a forename'; 
    }
    else if (empty($surname))
    {
        $surname_err = 'Please enter a surname'; 
    }
    else
    {
        // Combine forename and surname into a full name
        $name = $forname . ' ' . $surname;
        
        // Hash the password
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Prepare SQL statement to insert user data
        $sql = 'INSERT INTO Users (name, email, password) VALUES (:name, :email, :password)';
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hash); 
        
        // Execute the statement
        try {
            $stmt->execute();
            echo 'User registered successfully';
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            echo 'An error occurred while registering the user.';
        }
    }
}