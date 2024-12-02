<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Create a blank image with white background
$imageWidth = 800;
$imageHeight = 600;
$image = imagecreatetruecolor($imageWidth, $imageHeight);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$blue = imagecolorallocate($image, 0, 0, 255);
$fontPath = 'fonts/Basketball.otf';  // Path to your OTF font

// Fill the background with white
imagefill($image, 0, 0, $white);

// Title
imagettftext($image, 16, 0, 250, 30, $blue, $fontPath, 'Monthly Expense Report');

// Add Expense Data
$expenses = [
    ['expense_detail' => 'Electricity Bill', 'expense_amount' => 150.50],
    ['expense_detail' => 'Water Bill', 'expense_amount' => 50.30],
    ['expense_detail' => 'Internet Bill', 'expense_amount' => 100.00],
];

$yPosition = 70;  // Starting position for text
$lineHeight = 25; // Height between lines

// Add headers for the expense table
imagettftext($image, 12, 0, 30, $yPosition, $black, $fontPath, 'Expense Detail');
imagettftext($image, 12, 0, 300, $yPosition, $black, $fontPath, 'Amount (RM)');
$yPosition += $lineHeight;

foreach ($expenses as $expense) {
    imagettftext($image, 12, 0, 30, $yPosition, $black, $fontPath, $expense['expense_detail']);
    imagettftext($image, 12, 0, 300, $yPosition, $black, $fontPath, number_format($expense['expense_amount'], 2));
    $yPosition += $lineHeight;
}

// Total Expense
$total_expense = array_sum(array_column($expenses, 'expense_amount'));
imagettftext($image, 12, 0, 30, $yPosition, $black, $fontPath, "Total Expense: RM " . number_format($total_expense, 2));

// QR Code Text (optional: add QR code image)
$qrCodeText = 'Make Payment to Mr. Adib via QR Code:';
imagettftext($image, 12, 0, 30, $yPosition + $lineHeight + 30, $black, $fontPath, $qrCodeText);

// You can add an actual QR code image here using `imagecopy()` if needed
// Example (assuming qr.jpg is in the same directory):
$qrImage = imagecreatefromjpeg('qr.jpg');
imagecopy($image, $qrImage, 100, $yPosition + $lineHeight + 60, 0, 0, 100, 100);

// Output the image to the browser
header('Content-Type: image/png');
imagepng($image);  // Outputs the image as PNG to the browser

// Clean up
imagedestroy($image);
imagedestroy($qrImage);
?>
