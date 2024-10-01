<?php
// Include the database connection
include 'DB_Connection.php';

// Start the session
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    // Get the email and password from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL query to select the user by email
    $sql = "SELECT * FROM Users WHERE email = :email";

    try 
    {
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':email', $email);
        
        // Execute the statement
        $stmt->execute();
        
        // Check if the email exists
        if ($stmt->rowCount() > 0) 
        {
            // Fetch the user data
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Verify the password
            if (password_verify($password, $user['password'])) 
            {
                // Store user information in session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
        
                // Redirect to dashboard.php
                header("Location: /dashboard.php");
                exit();
            } 
            else 
            {
            // Redirect to login22.php with password error message
            header("Location: /login22.php?error=Invalid password");
            exit();
            }
        } 
        else 
        {
            // Redirect to login22.php with email error message
            header("Location: /login22.php?error=Invalid email");
            exit();
        }
    } 
    catch (PDOException $e) 
    {
        echo "Error: " . $e->getMessage();
    }
} 
else 
{
    // Redirect to login22.php if the form is not submitted
    header("Location: /login22.php");
    exit();
}

// Close the connection
$conn = null;
?>