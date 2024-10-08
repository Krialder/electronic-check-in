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

    function fetchTableData($conn, $tableName) 
    {
        $stmt = $conn->query("SELECT * FROM $tableName");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function displayTable($data, $tableName) 
    {
        if (count($data) > 0) 
        {
            echo "<h2>$tableName</h2>";
            echo "<table border='1'>";
            echo "<tr>";
            foreach (array_keys($data[0]) as $header) 
            {
                echo "<th>$header</th>";
            }
            echo "</tr>";
            foreach ($data as $row) 
            {
                echo "<tr>";
                foreach ($row as $cell) 
                {
                    echo "<td>$cell</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } 
        else 
        {
            echo "<p>No data found in $tableName</p>";
        }
    }

    try 
    {
        $tables = ['accesslogs', 'checkin', 'events', 'reports', 'rfiddevices', 'users'];
        foreach ($tables as $table) 
        {
            $data = fetchTableData($conn, $table);
            displayTable($data, $table);
        }
    } 
    catch (PDOException $e) 
    {
        echo "Error: " . $e->getMessage();
    }
    ?>
</body>
</html>