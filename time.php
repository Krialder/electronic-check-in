<?php
function updateTime() 
{
    while (true) 
    {
        $current_time = date("Y-m-d H:i:s");
        echo "The current date and time is " . $current_time . "<br>";
        sleep(1); 
    }
}

// updateTime();
?>
