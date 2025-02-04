<?php
session_start();

$apiKey = "FTFkCwyMmy8MMnAQcQWaYG9AR99z3hq4PBfq";

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['artist']) || isset($_GET['venue']) || isset($_GET['tour']) || isset($_GET['year']))) {
    $_SESSION['searchParams'] = [
        'artist' => $_GET['artist'] ?? '',
        'venue' => $_GET['venue'] ?? '',
        'tour' => $_GET['tour'] ?? '',
        'year' => $_GET['year'] ?? '',
    ];
}

if (!isset($_SESSION['searchParams'])) {
    $_SESSION['searchResults'] = ["error" => "No search criteria provided"];
    $_SESSION['step'] = 1;
    header("Location: ../index.php");
    exit();
}
$params = $_SESSION['searchParams'];

$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;

$queryParams = array_filter([
    'artistName' => $params['artist'],
    'venueName' => $params['venue'],
    'tourName' => $params['tour'],
    'year' => $params['year'],
]);

$queryString = http_build_query($queryParams);
$url = "https://api.setlist.fm/rest/1.0/search/setlists?$queryString&p=$page&sort=relevance";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "x-api-key: $apiKey",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpStatus == 200) {
    $newResults = json_decode($response, true);

    if (!empty($_SESSION['searchResults']['setlist'])) {
        $_SESSION['searchResults']['setlist'] = array_merge($_SESSION['searchResults']['setlist'], $newResults['setlist']);
    } else {
        $_SESSION['searchResults'] = $newResults;
    }
} else {
    $_SESSION['searchResults'] = ["error" => "Failed to fetch data. HTTP Status: $httpStatus"];
}

$_SESSION['step'] = 2;

if (!empty($_GET['ajax'])) {
    echo json_encode(["status" => "success"]);
    exit();
}

header("Location: ../index.php");
exit();
?>
