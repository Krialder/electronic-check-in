<?php
include 'DB_Connection.php';

// Initialize email and password variables
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Create SQL query to check email and password
    $sql = "SELECT * FROM Users WHERE email = :email AND password = :password";

    try {
        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        
        // Execute the statement
        $stmt->execute();
        
        // Check if any rows are returned
        if ($stmt->rowCount() > 0) 
        {
            // Redirect to dashboard.html
            echo '<a href="file:///C:/xampp/htdocs/dashboard.html">Go to Dashboard</a>';
            exit();
        } 
        else 
        {
            // Redirect to login2.html with error message
            header("Location: /login2.html");
            exit();
        }
    } catch (PDOException $e) 
    {
        // Redirect to login2.html with error message
        header("Location: /login2.html");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <form method="post" action="login2.php">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br>
        <button type="submit">Login</button>
    </form>
    <?php
    if (isset($_GET['error'])) 
    {
        echo '<p style="color:red;">' . htmlspecialchars($_GET['error']) . '</p>';
    }
    ?>
</body>
</html>