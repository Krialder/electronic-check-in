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
    exit(); 
}

if (isset($_POST['register']))
{
    // Retrieve form data
    $forname = $_POST['Vorname'];
    $surname = $_POST['Nachname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    // Validate form data
    if ($password !== $password2)
    {  
        $password_err = 'Passwörter stimmen nicht überein'; 
    }
    else if (empty($password))
    {
        $password_err = 'Bitte geben Sie ein Passwort ein'; 
    }
    else if (empty($email))
    {
        $email_err = 'Bitte geben Sie eine E-Mail-Adresse ein'; 
    }
    else if (empty($forname))
    {
        $forname_err = 'Bitte geben Sie einen Vornamen an'; 
    }
    else if (empty($surname))
    {
        $surname_err = 'Bitte geben Sie einen Nachnamen an'; 
    }
    else
    {
        // Combine forename and surname into a full name
        $name = $forname . ' ' . $surname;
        
        // Hash the password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare SQL statement to insert user data
        $sql = 'INSERT INTO Users (name, email, password) VALUES (:name, :email, :password)';
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        // Use hashed password
        $stmt->bindParam(':password', $hash); 
        
        // Execute the statement
        $stmt->execute();       
    }
}