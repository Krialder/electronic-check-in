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
        $row = $result->fetch_assoc();
        echo "Welcome, " . $row['name'] . "!<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Phone: " . $row['phone'] . "<br>";
        echo "RFID Tag: " . $row['rfid_tag'] . "<br>";
        echo "Role: " . $row['role'] . "<br>";
        echo "Created At: " . $row['created_at'] . "<br>";
        echo "Updated At: " . $row['updated_at'] . "<br>";
    }
    else 
    {
        echo "Invalid email or password.";
    }

    $stmt->close();
}

$conn->close();
?>
