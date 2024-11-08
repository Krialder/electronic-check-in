<?php

//Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

//debugging statement
error_log('register.php beginning');

// Inlucde the database connection from the file
include 'DB_Connection.php';

// Define variables and initialize with empty values
$forname_err = $surname_err = $password_err = $name_err = '';

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
        $name_err = 'Der Benutzername ist nicht verfügbar';
        // Display the error message and stop execution
        echo "<script>alert('$name_err'); window.location.href='register.html';</script>";
        exit;
    } else {
        // Check if passwords match
        if ($password !== $password2)
        {
            $password_err = 'Passwörter stimmen nicht überein';
        }

        // Check if name is empty
        else if (empty($password))
        {
            $password_err = 'Bitte geben Sie ein Passwort ein';
        }

        // Check if forname contains only letters
        else if (!ctype_alpha($forname))
        {
            $forname_err = 'Der Vorname darf nur Buchstaben enthalten';
        }

        // Check if forname is empty
        else if (empty($forname))
        {
            $forname_err = 'Bitte geben Sie einen Vornamen an';
        }

        //Check if surname contains only letters
        else if (!ctype_alpha($surname))
        {
            $surname_err = 'Der Nachname darf nur Buchstaben enthalten';
        }

        // Check if surname is empty
        else if (empty($surname))
        {
            $surname_err = 'Bitte geben Sie einen Nachnamen an';
        }

        else
        {
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
}

$conn = null;
?>