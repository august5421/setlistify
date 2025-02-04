<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$artistImage = $_SESSION['artistImage'] ?? '../assets/placeholder.png';
$textToPrint = isset($_GET['text']) ? $_GET['text'] : 'Default Text';
$base64Mode = isset($_GET['base64']) && $_GET['base64'] === 'true'; 
if (filter_var($artistImage, FILTER_VALIDATE_URL)) {
    $imageData = file_get_contents($artistImage);
    if (!$imageData) {
        die("Error: Unable to fetch image from URL.");
    }
    $image = imagecreatefromstring($imageData);
} else {
    if (!file_exists($artistImage)) {
        die("Error: Image file not found!");
    }
    $ext = strtolower(pathinfo($artistImage, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpeg':
        case 'jpg':
            $image = imagecreatefromjpeg($artistImage);
            break;
        case 'png':
            $image = imagecreatefrompng($artistImage);
            break;
        case 'gif':
            $image = imagecreatefromgif($artistImage);
            break;
        default:
            die("Unsupported image type.");
    }
}

if (!$image) {
    die("Error: Unable to create image resource.");
}

$width = imagesx($image);
$height = imagesy($image);
$overlay = imagecreatetruecolor($width, $height);
imagesavealpha($overlay, true);
$transparentColor = imagecolorallocatealpha($overlay, 0, 0, 0, 20);
imagefill($overlay, 0, 0, $transparentColor);
imagecopymerge($image, $overlay, 0, 0, 0, 0, $width, $height, 50);

$textColor = imagecolorallocate($image, 255, 255, 255);
$font = __DIR__ . '/font.ttf';

if (!file_exists($font)) {
    die("Error: Font file not found!");
}

$fontSize = 40;
$lineHeight = $fontSize * 1.5;
$words = explode(' ', $textToPrint);
$currentY = 50;
$dateInLine = false;
$dateText = '';

foreach ($words as $word) {
    if (strpos($word, '(') !== false || strpos($word, ')') !== false || $dateInLine) {
        $dateInLine = true;
        $dateText .= $word . ' ';  
        
        if (strpos($word, ')') !== false) {
            imagettftext($image, $fontSize, 0, 20, $currentY, $textColor, $font, trim($dateText));
            $currentY += $lineHeight;
            $dateInLine = false;
            $dateText = '';  
        }
    } else {
        imagettftext($image, $fontSize, 0, 20, $currentY, $textColor, $font, $word);
        $currentY += $lineHeight;
    }
}

if ($base64Mode) {
    ob_start(); 
    imagejpeg($image);
    $imageData = ob_get_contents();
    ob_end_clean(); 

    $base64Image = base64_encode($imageData);
    header('Content-Type: application/json');
    echo json_encode(['image' => 'data:image/jpeg;base64,' . $base64Image]); 
} else {
    header("Content-Type: image/jpeg");
    imagejpeg($image);
}

imagedestroy($image);
imagedestroy($overlay);
?>
