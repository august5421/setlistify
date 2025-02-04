<?php
session_start();

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $clientId = '981fc09664874b72982e45616a0e0111'; 
    $clientSecret = '116968bf0b664e7daae8975a3d3ff821'; 
    $redirectUri = 'http://localhost/callback.php'; 

    $auth = base64_encode($clientId . ':' . $clientSecret);
    $data = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $redirectUri
    ];

    $options = [
        'http' => [
            'header' => [
                "Authorization: Basic $auth",
                "Content-Type: application/x-www-form-urlencoded"
            ],
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents('https://accounts.spotify.com/api/token', false, $context);
    $responseData = json_decode($response, true);

    if (isset($responseData['access_token'])) {
        $accessToken = $responseData['access_token'];
        $_SESSION['spotifyAccessToken'] = $accessToken;

        $userDataResponse = file_get_contents('https://api.spotify.com/v1/me', false, stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer $accessToken"
            ]
        ]));
        $userData = json_decode($userDataResponse, true);
        $_SESSION['spotifyUserID'] = $userData['id'] ?? null;
        $_SESSION['spotifyAvatar'] = $userData['images'][0]['url'] ?? 'default-avatar.jpg';
        $_SESSION['spotifyUsername'] = $userData['display_name'] ?? 'Spotify User';

        header('Location: index.php');
        exit();
    } else {
        echo 'Error fetching access token.';
    }
}
?>
