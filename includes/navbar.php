<?php
    session_start();
    $clientId = '981fc09664874b72982e45616a0e0111'; 
    $redirectUri = 'http://localhost/callback.php';
    $authUrl = "https://accounts.spotify.com/authorize?response_type=code&client_id=$clientId&redirect_uri=" . urlencode($redirectUri) . "&scope=user-read-private user-read-email playlist-modify-public playlist-modify-private ugc-image-upload";
    $logoutUrl = 'scripts/logout.php'; 

    $_SESSION['authUrl'] = $authUrl;

    if (!isset($_SESSION['step'])) {
        $_SESSION['step'] = 1;
    }
    if (isset($_GET['next'])) {
        $_SESSION['step'] = $_GET['next'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Bootstrap Template</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand display-6 logoFont" href="#">Setlist.ify</a>
        <ul class="navbar-nav ms-auto">
            <?php if (isset($_SESSION['spotifyAccessToken'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $_SESSION['spotifyAvatar']; ?>" alt="User Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo $logoutUrl; ?>">Logout</a>
                        </li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-info" href="<?php echo $authUrl; ?>" role="button">Login with Spotify</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
