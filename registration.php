<?php

session_start();

error_reporting(E_ALL);

ini_set('display_errors', 1);

error_log('registration.php beginning');

include 'DB_Connection.php';

$user_role = $_SESSION['role'] === 'admin';

if (!$user_role) 
{
    exit();
}
if (isset($_POST['register']))
{
    $forname = $_POST['Vorname'];
    $lastname = $_POST['Nachname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

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
    else if (empty($lastname))
    {
        $lastname_err = 'Bitte geben Sie einen Nachnamen an';
    }
    else
    {
        $name = $_POST['name'] ($forname . ' ' . $lastname);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = 'INSERT INTO Users (name, email, password) VALUES (:name, :email, :password)';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();       
    }
}