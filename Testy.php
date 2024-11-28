<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log('testy.php beginning');
echo('hi<br/>');
session_start();

if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
{
    header('location: dashboard.html');
    exit;
}

include 'DB_Connection.php';

$name = $password = '';
$name_err = $password_err = $login_err = '';

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    error_log('Form submitted');
    echo('form submitted<br/>');

    if(empty(trim($_POST['name'])))
    {
        $name_err = 'Please enter name.';
        error_log('name is empty');
        echo('name is empty<br/>');
    }
    else
    {
        $name = trim($_POST['name']);
    }
    
    if(empty(trim($_POST['password'])))
    {
        $password_err = 'Please enter your password.';
        error_log('Password is empty');
        echo('password is empty<br/>');
    } 
    else
    {
        $password = trim($_POST['password']);
    }

    if(empty($name_err) && empty($password_err))
    {
        $sql = 'SELECT user_id, name, password, email, role FROM Users WHERE name = :name';
        
        if($stmt = $conn->prepare($sql))
        {
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        
            if($stmt->execute())
            {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                error_log('Hashed password from DB: ' . $user['password']);
                echo 'Hashed password from DB: ' . $user['password'] . '<br/>';

                if (password_verify($password, $user['password'])) 
                {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    error_log('Redirecting to /dashboard.html');
                    echo('Redirecting to /dashboard.html<br/>');
                        
                    header('location: /dashboard.html');
                    exit();
                }
                else
                {
                    $password_err = 'The password you entered was not valid.';
                }
        
                unset($stmt);
            }
        }
    }
}
$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ...existing code... -->
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dark-mode.css">
    <link rel="stylesheet" href="lightmode.css">
    <link rel="stylesheet" href="common.css"> <!-- New stylesheet link -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            if (localStorage.getItem('darkMode') === 'enabled') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
        });
    </script>
</head>
<body>
    <!-- ...existing code... -->
</body>
</html>