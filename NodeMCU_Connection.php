<?php

function checkNodeMCUConnection($nodeMCU_IP) 
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$nodeMCU_IP/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}

function getRFIDFromNodeMCU($nodeMCU_IP) 
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://$nodeMCU_IP/getRFID");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) 
    {
        echo 'Curl error: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}
?>
