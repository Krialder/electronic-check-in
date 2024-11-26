<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection from the file
include 'DB_Connection.php';

// Define variables and initialize with empty values
$forname_err = $surname_err = $password_err = $name_err = '';

if (isset($_POST['register'])) {
    $forname = $_POST['forname'];
    $surname = $_POST['surname'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $role = 'guest';
    $user_rfid_tag = NULL;
    $phone = NULL;

    // Check if username already exists in Guest or Users table
    $sql = "SELECT COUNT(*) FROM Guest WHERE name = ? UNION SELECT COUNT(*) FROM Users WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $name]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $name_err = 'Der Benutzername ist nicht verfügbar';
        echo "<script>alert('$name_err'); window.location.href='register.html';</script>";
        exit;
    } else {
        // Validate inputs
        if ($password !== $password2) {
            $password_err = 'Passwörter stimmen nicht überein';
        } elseif (empty($password)) {
            $password_err = 'Bitte geben Sie ein Passwort ein';
        } elseif (!ctype_alpha($forname)) {
            $forname_err = 'Der Vorname darf nur Buchstaben enthalten';
        } elseif (empty($forname)) {
            $forname_err = 'Bitte geben Sie einen Vornamen an';
        } elseif (!ctype_alpha($surname)) {
            $surname_err = 'Der Nachname darf nur Buchstaben enthalten';
        } elseif (empty($surname)) {
            $surname_err = 'Bitte geben Sie einen Nachnamen an';
        } else {
            // Find the first non-existing user ID
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

            // Insert the new guest into the database
            $sql = "INSERT INTO Guest (user_id, name, email, phone, rfid_tag, role, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$new_user_id, $name, $email, $phone, $user_rfid_tag, $role, $hashed_password]);

            if ($stmt->rowCount() > 0) {
                header('location: testy.html');
                exit;
            } else {
                echo "<script>alert('Registration failed. Please try again.'); window.location.href='register.html';</script>";
            }
        }
    }
}

$conn = null;
?>