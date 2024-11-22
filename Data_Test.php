<?php

include 'DB_Connection.php';

// Function to fetch and display data from a table
function fetchData($conn, $table) 
{
    $sql = "SELECT * FROM $table";
    $stmt = $conn->query($sql);

    if ($stmt->rowCount() > 0) 
    {
        echo "<h2>Data from $table table:</h2>";
        echo "<table border='1'><tr>";

        // Fetch field names
        for ($i = 0; $i < $stmt->columnCount(); $i++) 
        {
            $columnMeta = $stmt->getColumnMeta($i);
            echo "<th>{$columnMeta['name']}</th>";
        }
        echo "</tr>";

        // Fetch rows
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
        {
            echo "<tr>";
            foreach ($row as $cell) 
            {
                echo "<td>$cell</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
    } 
    else 
    {
        echo "0 results for $table table<br>";
    }
}

// Fetch and display data from each table
fetchData($conn, "Users");
fetchData($conn, "Events");
fetchData($conn, "CheckIn");
fetchData($conn, "RFIDDevices");

// Close the connection
$conn = null;
?>