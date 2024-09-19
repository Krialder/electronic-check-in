<?php
include 'DBConnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) 
    {
        header("Location: dashboard.html");
        exit();
    } 
    else 
    {
        echo "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>
