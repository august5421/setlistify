<?php
session_start(); 

$allowed_vars = ['spotifyAccessToken', 'spotifyUserID', 'spotifyAvatar', 'spotifyUsername', 'authUrl'];

foreach ($_SESSION as $key => $value) {
    if (!in_array($key, $allowed_vars)) {
        unset($_SESSION[$key]);
    }
}

header("Location: ../index.php"); 
exit();

?>
