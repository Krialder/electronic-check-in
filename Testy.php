<?php

//Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

//debugging statement
error_log('testy.php beginning');
echo('hi<br/>');
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
{
    header('location: dashboard.html');
    exit;
}

// Include config file
include 'DB_Connection.php';

// Define variables and initialize with empty values
$name = $password = '';
$name_err = $password_err = $login_err = '';

// Processing form data when form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    //debugging statement
    error_log('Form submitted');
    echo('form submitted<br/>');

    // Check if name is empty
    if(empty(trim($_POST['name'])))
    {
        $name= 'Please enter name.';
        //debugging statement
        error_log('name is empty');
        echo('name is empty<br/>');
    }
    else
    {
        $name = trim($_POST['name']);
    }
    
    // Check if password is empty
    if(empty(trim($_POST['password'])))
    {
        $password_err = 'Please enter your password.';
        //debugging statement
        error_log('Password is empty');
        echo('password is empty<br/>');
    } 
    else
    {
        $password = trim($_POST['password']);
    }
    
    // Validate credentials
    if(empty($name_err) && empty($password_err))
    {
        // Prepare a select statement
        $sql = 'SELECT user_id, name, password, email, role FROM Users WHERE name = :name';
        
        if($stmt = $conn->prepare($sql))
        {
            // Bind variables to the prepared statement as parameters
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        
            // Check if name exists, if yes then verify password
            if($stmt->execute())
            {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                // Print hashed password from the database
                error_log('Hashed password from DB: ' . $user['password']);
                echo 'Hashed password from DB: ' . $user['password'] . '<br/>';

                // Verify password using password_verify
                if (password_verify($password, $user['password'])) 
                {
                    // Password is correct, so start a new session
                    session_start();
                    
                    // Store data in session variables
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id'] = $user['user_id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    // Debugging statement
                    error_log('Redirecting to /dashboard.html');
                    echo('Redirecting to /dashboard.html<br/>');
                        
                    // Redirect user to welcome page
                    header('location: /dashboard.html');
                    exit();
                }
                else
                {
                    // Password is not valid, display a generic error message
                    $password_err = 'The password you entered was not valid.';
                }
        
                // Close statement
                unset($stmt);
            }
        }
    }
}
// Close connection
$conn = null;
?>