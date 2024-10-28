<?php

//Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

//debugging statement
error_log('register.php beginning');

// Inlucde the database connection from the file
include 'DB_Connection.php';


// Define variables and initialize with empty values
if (isset($_POST['register']))
{
    $forname = $_POST['forname'];
    $surname = $_POST['surname'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $role = 'guest';
    $user_rfid_tag = NULL;
    $phone = NULL;

    // Check if passwords match
    if ($password !== $password2)
    {
        $password_err = 'Passwörter stimmen nicht überein';
        echo $password_err;
    }

    // Check if name is empty
    else if (empty($password))
    {
        $password_err = 'Bitte geben Sie ein Passwort ein';
        echo $password_err;
    }

    // Check if forname is empty
    else if (empty($forname))
    {
        $forname_err = 'Bitte geben Sie einen Vornamen an';
        echo $forname_err;
    }

    // Check if surname is empty
    else if (empty($surname))
    {
        $surname_err = 'Bitte geben Sie einen Nachnamen an';
        echo $surname_err;
    }

    //
    else
    {
        // Hash the password before storing it in the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL statement to insert the new guest into the database
        $sql = "INSERT INTO Guest (name, email, phone, rfid_tag, role, password) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        // Bind the parameters to the SQL query
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $email);
        $stmt->bindValue(3, $phone);
        $stmt->bindValue(4, $user_rfid_tag);
        $stmt->bindValue(5, $role);
        $stmt->bindValue(6, $hashed_password);

        // Execute the statement and check if the registration was successful
        if ($stmt->execute())
        {
            echo 'Registration successful';
            header('location: testy.html');
            exit;
        }
        else
        {
            // Output error message if the registration failed
            echo 'Error: ' . $stmt->errorInfo()[2];
        }
        $stmt = null;
    }
}

$conn = null;
?>