<?php
session_start();

$setlistApiKey = "FTFkCwyMmy8MMnAQcQWaYG9AR99z3hq4PBfq";
$spotifyClientId = "981fc09664874b72982e45616a0e0111";
$spotifyClientSecret = "116968bf0b664e7daae8975a3d3ff821";

function getSpotifyAccessToken($clientId, $clientSecret) {
    $url = 'https://accounts.spotify.com/api/token';
    $postFields = [
        'grant_type' => 'client_credentials'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$clientId:$clientSecret"),
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['access_token'] ?? null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['setlistId'])) {
    $setlistId = $_GET['setlistId'];  
    $setlistArtist = urlencode($_GET['setlistArtist']);
    $url = "https://api.setlist.fm/rest/1.0/setlist/$setlistId";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "x-api-key: $setlistApiKey",
        "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpStatus == 200) {
        $setlistDetails = json_decode($response, true);

        $spotifyAccessToken = getSpotifyAccessToken($spotifyClientId, $spotifyClientSecret);

        if ($spotifyAccessToken) {
            $artistUrl = "https://api.spotify.com/v1/search?q=artist:$setlistArtist&type=artist&limit=1";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $artistUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $spotifyAccessToken"
            ]);
            $artistResponse = curl_exec($ch);
            curl_close($ch);

            $artistData = json_decode($artistResponse, true);
            
            if (!empty($artistData['artists']['items'])) {
                $artistImage = $artistData['artists']['items'][0]['images'][0]['url'] ?? 'path_to_default_artist_image.jpg';
            } else {
                $artistImage = 'path_to_default_artist_image.jpg';
            }

            $_SESSION['artistImage'] = $artistImage;

            $trackDetails = [];
            $totalDurationMs = 0;
            foreach ($setlistDetails['sets']['set'] as $set) {
                foreach ($set['song'] as $song) {
                    $songTitle = urlencode($song['name']);
                    $spotifyUrl = "https://api.spotify.com/v1/search?q=track:$songTitle%20artist:$setlistArtist&type=track&limit=1";

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $spotifyUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        "Authorization: Bearer $spotifyAccessToken"
                    ]);
                    $spotifyResponse = curl_exec($ch);
                    curl_close($ch);

                    $spotifyData = json_decode($spotifyResponse, true);
                    
                    if (!empty($spotifyData['tracks']['items'])) {
                        $track = $spotifyData['tracks']['items'][0];
                        $trackDurationMs = $track['duration_ms']; 
                        $totalDurationMs += $trackDurationMs;
                        $trackDetails[] = [
                            'title' => $track['name'],
                            'artist' => $track['artists'][0]['name'],
                            'album' => $track['album']['name'],
                            'coverArt' => $track['album']['images'][0]['url'],
                            'uri' => $track['uri'],
                        ];
                    } else {
                        $trackDetails[] = [
                            'title' => $song['name'],
                            'artist' => 'Unknown',
                            'album' => 'Unknown',
                            'coverArt' => 'path_to_default_image.jpg',
                            'message' => 'No exact match found on Spotify'
                        ];
                    }
                }
            }

            $totalDurationSec = floor($totalDurationMs / 1000);
            $totalDurationHours = floor($totalDurationSec / 3600);
            $totalDurationSec = $totalDurationSec % 3600;
            $totalDurationMin = floor($totalDurationSec / 60);
            $totalDurationSec = $totalDurationSec % 60;
            $totalDuration = sprintf('%02d:%02d:%02d', $totalDurationHours, $totalDurationMin, $totalDurationSec);
            $_SESSION['totalDuration'] = $totalDuration;
            $_SESSION['setlistDetails'] = $setlistDetails;
            $_SESSION['trackDetails'] = $trackDetails;
        } else {
            $_SESSION['setlistDetails'] = ["error" => "Failed to get Spotify Access Token"];
        }
    } else {
        $_SESSION['setlistDetails'] = ["error" => "Failed to fetch setlist details. HTTP Status: $httpStatus"];
    }

    $_SESSION['step'] = 3;
    header("Location: ../index.php");
    exit();
} else {
    $_SESSION['setlistDetails'] = ["error" => "Setlist ID not provided"];
    $_SESSION['step'] = 2; 
    header("Location: ../index.php");
    exit();
}
