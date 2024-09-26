<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Information</title>
</head>
<body>
    <h1>Database Information</h1>

   <?php
include 'DB_Connection.php';

try 
{
    $conn->exec("USE kde_test2");

    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<h2>Tables in kde_test2 database:</h2>";
    echo "<ul>";
    foreach ($tables as $table) 
	{
        echo "<li>$table</li>";
    }
    echo "</ul>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
</body>
</html>