<?php

session_start();
if (!isset($_SESSION['spotifyAccessToken'])) {
    die('Authorization required');
}

$playlistName = $_POST['playlistName'];
$privacySwitch = $_POST['privacySwitchHidden']; 
$playlistImage = $_POST['playlistImage'];
$songList = json_decode($_POST['playlistSongs'], true);
$spotifyApiUrl = 'https://api.spotify.com/v1/me';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $spotifyApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['spotifyAccessToken'],
]);

$userResponse = curl_exec($ch);
$userData = json_decode($userResponse, true);

if (isset($userData['id'])) {
    $userId = $userData['id'];
} else {
    $_SESSION['playlistError'] = 'Error fetching user data.';
    header("Location: ../index.php");
    exit;
}

$playlistUrl = 'https://api.spotify.com/v1/users/' . $userId . '/playlists';
$playlistData = [
    'name' => $playlistName,
    'description' => 'Generated playlist from ' . $playlistName . '. This playlist was generated by setlist.ify',
    'public' => $privacySwitch === 'public',
];

curl_setopt($ch, CURLOPT_URL, $playlistUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($playlistData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['spotifyAccessToken'],
    'Content-Type: application/json',
]);

$playlistResponse = curl_exec($ch);
$playlistData = json_decode($playlistResponse, true);

if (isset($playlistData['id'])) {
    $playlistId = $playlistData['id'];
    $_SESSION['playlistID'] = $playlistId;
} else {
    $_SESSION['playlistError'] = 'Error creating playlist.';
    header("Location: ../index.php");
    exit;
}

$trackUris = [];
foreach ($songList as $song) {
    if (isset($song['uri'])) {
        $trackUris[] = $song['uri'];
    }
}

if (!empty($trackUris)) {
    $addTracksUrl = 'https://api.spotify.com/v1/playlists/' . $playlistId . '/tracks';
    $trackData = [
        'uris' => $trackUris,
    ];

    curl_setopt($ch, CURLOPT_URL, $addTracksUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($trackData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['spotifyAccessToken'],
        'Content-Type: application/json',
    ]);

    $addTracksResponse = curl_exec($ch);
    if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 201) {
        $_SESSION['playlistError'] = 'Error adding tracks to playlist. Response: ' . $addTracksResponse;
        header("Location: ../index.php");
        exit;
    }
}

if (!empty($playlistImage)) {
    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $playlistImage);
    
    $coverUrl = 'https://api.spotify.com/v1/playlists/' . $playlistId . '/images';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $coverUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');  
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['spotifyAccessToken'],
        'Content-Type: image/jpeg',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData); 
    $verbose = fopen('php://temp', 'w+'); 
    curl_setopt($ch, CURLOPT_VERBOSE, true); 
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $imageResponse = curl_exec($ch);

    unlink($tempImageFile);
}

curl_close($ch);

$_SESSION['step'] = 4;
header("Location: ../index.php");
exit;
?>
