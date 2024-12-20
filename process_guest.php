<?php
session_start();

include 'DB_Connection.php';

// Check if the user role is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') 
{
    exit('Unauthorized access');
}


$error_msg = '';

// Process and POST the Guest form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // Retrieve form data
    $userId = $_POST['user_id'] ?? null;
    $name = $_POST['name'] ?? null; 
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $rfid_tag = $_POST['rfid_tag'] ?? null;
    $password = $_POST['password'];
    $password2 = $_POST['password2'] ?? null;
    $role = $_POST['role'];

    // Validate form data
    if (empty($name) || empty($rfid_tag) || empty($password) || empty($role)) 
    {
        $error_msg = 'Please fill in all required fields';
    } 
    elseif ($password2 !== null && $password !== $password2) 
    {
        $error_msg = 'Passwörter stimmen nicht überein';
    } 
    else 
    {
        // Check if RFID tag is unique
        $sql = 'SELECT COUNT(*) FROM Users WHERE rfid_tag = :rfid_tag';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':rfid_tag', $rfid_tag);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) 
        {
            $error_msg = 'RFID tag must be unique';
        } 
        else 
        {
            // Hash the password
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Find the first available user ID
            $stmt = $conn->query('SELECT user_id FROM Users ORDER BY user_id');
            $existingIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $newUserId = 1;
            foreach ($existingIds as $id) 
            {
                if ($id != $newUserId) 
                {
                    break;
                }
                $newUserId++;
            }

            if ($userId === null) 
            {
                // Registration: Prepare SQL statement to insert user data
                $sql = 'INSERT INTO Users (user_id, name, email, phone, rfid_tag, password, role) VALUES (:user_id, :name, :email, :phone, :rfid_tag, :password, :role)';
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':user_id', $newUserId);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':rfid_tag', $rfid_tag);
                $stmt->bindParam(':password', $hash);
                $stmt->bindParam(':role', $role);
                $stmt->execute();
            } 
            else 
            {
                // Transfer guest to Users table
                $conn->beginTransaction();
                try 
                {
                    // Fetch guest data from Guest table
                    $sql = 'SELECT name, email, phone, rfid_tag, password, role FROM Guest WHERE user_id = :user_id';
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->execute();
                    $guestData = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Use guest data if form data is not provided
                    $name = $name ?: $guestData['name'];
                    $email = $email ?: $guestData['email'];
                    $phone = $phone ?: $guestData['phone'];
                    $rfid_tag = $rfid_tag ?: $guestData['rfid_tag'];
                    $password = $password ?: $guestData['password'];
                    $role = $role ?: $guestData['role'];

                    // Insert into Users table
                    $sql = 'INSERT INTO Users (user_id, name, email, phone, rfid_tag, password, role) VALUES (:user_id, :name, :email, :phone, :rfid_tag, :password, :role)';
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':user_id', $newUserId);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':rfid_tag', $rfid_tag);
                    $stmt->bindParam(':password', $hash);
                    $stmt->bindParam(':role', $role);
                    $stmt->execute();

                    // Delete from Guest table
                    $sql = 'DELETE FROM Guest WHERE user_id = :user_id';
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':user_id', $userId);
                    $stmt->execute();

                    $conn->commit();
                    reassignGuestIds($conn); 
                    header('Location: /account-settings.html?success=1');
                    exit();
                } catch (Exception $e) 
                {
                    $conn->rollBack();
                    $error_msg = 'Error during operation: ' . $e->getMessage();
                }
            }
        }
    }

    // Redirect back to account-settings.html with error message and user ID
    header('Location: /account-settings.html?error=' . urlencode($error_msg) . '&user_id=' . urlencode($userId));
    exit();
}

// Function to reassign Guest IDs sequentially
function reassignGuestIds($conn) 
{
    $stmt = $conn->query('SELECT user_id FROM Guest ORDER BY user_id');
    $guestIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $newId = 1;
    foreach ($guestIds as $id) 
    {
        if ($id != $newId) 
        {
            $updateStmt = $conn->prepare('UPDATE Guest SET user_id = :new_id WHERE user_id = :old_id');
            $updateStmt->bindParam(':new_id', $newId);
            $updateStmt->bindParam(':old_id', $id);
            $updateStmt->execute();
        }
        $newId++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Process Guest</title>
</head>
<body>
    <?php if (!empty($error_msg)) echo '<p>' . $error_msg . '</p>'; ?>
</body>
</html>