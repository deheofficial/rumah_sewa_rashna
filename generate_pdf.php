<?php
// Include necessary libraries
$conn = new mysqli("localhost", "root", "", "house_expenses");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch data
$expenses = $conn->query("SELECT * FROM expenses");
$tenants = $conn->query("SELECT COUNT(*) as total_tenants FROM tenants")->fetch_assoc()['total_tenants'];
$total_expense = $conn->query("SELECT SUM(expense_amount) as total FROM expenses")->fetch_assoc()['total'];
$share_per_tenant = $total_expense / ($tenants ?: 1);

// Path to the QR code image
$qrImage = "qr.jpg";

// Set image dimensions
$image_width = 800;
$image_height = 1000;
$image = imagecreate($image_width, $image_height);

// Set background and text colors
$background_color = imagecolorallocate($image, 255, 255, 255); // White background
$text_color = imagecolorallocate($image, 0, 0, 0); // Black text
$header_color = imagecolorallocate($image, 0, 0, 128); // Dark blue for headers

// Add title
$font_path = 'arial.ttf'; // Path to your .ttf font

// Check if the font exists before proceeding
if (!file_exists($font_path)) {
    die("Font file not found: " . $font_path);
}

// Add title text with error handling
imagettftext($image, 16, 0, 250, 30, $header_color, $font_path, 'Monthly Expense Report');

// Draw expense table headers
$y_position = 60;
$line_height = 30;

imagettftext($image, 12, 0, 30, $y_position, $header_color, $font_path, 'Expense Detail');
imagettftext($image, 12, 0, 400, $y_position, $header_color, $font_path, 'Amount (RM)');

// Loop through expenses and add them to the image
while ($row = $expenses->fetch_assoc()) {
    $y_position += $line_height;
    imagettftext($image, 12, 0, 30, $y_position, $text_color, $font_path, $row['expense_detail']);
    imagettftext($image, 12, 0, 400, $y_position, $text_color, $font_path, number_format($row['expense_amount'], 2));
}

// Draw the summary
$y_position += $line_height * 2; // Space between table and summary
imagettftext($image, 12, 0, 30, $y_position, $header_color, $font_path, "Total Expense: RM " . number_format($total_expense, 2));
$y_position += $line_height;
imagettftext($image, 12, 0, 30, $y_position, $header_color, $font_path, "Total Tenants: " . $tenants);
$y_position += $line_height;
imagettftext($image, 12, 0, 30, $y_position, $header_color, $font_path, "Share per Tenant: RM " . number_format($share_per_tenant, 2));

// Add QR code
$qr_image = imagecreatefromjpeg($qrImage);
$qr_width = imagesx($qr_image);
$qr_height = imagesy($qr_image);

// Place the QR code at the bottom of the image
$qr_x = ($image_width - $qr_width) / 2; // Centered horizontally
$qr_y = $image_height - $qr_height - 50; // 50px from the bottom
imagecopy($image, $qr_image, $qr_x, $qr_y, 0, 0, $qr_width, $qr_height);

// Free memory
imagedestroy($qr_image);

// Output the image
header("Content-type: image/jpeg");
imagejpeg($image);

// Free memory
imagedestroy($image);
?>
