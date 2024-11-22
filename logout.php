<?php
// Start the session
session_start();

// Clear all session variables
$_SESSION = array();

// If the session is using cookies, clear the session cookie
if (ini_get('session.use_cookies')) 
{
    $params = session_get_cookie_params();
    // Set the session cookie to expire in the past
    setcookie('session_name', '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Redirect to the specified page
header('Location: /Testy.html');
exit();
?>