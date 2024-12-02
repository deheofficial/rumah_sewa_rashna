<?php
// Include FPDF from GitHub
require('fpdf/fpdf.php');
$conn = new mysqli("localhost", "root", "", "house_expenses");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch data
$expenses = $conn->query("SELECT * FROM expenses");
$tenants = $conn->query("SELECT COUNT(*) as total_tenants FROM tenants")->fetch_assoc()['total_tenants'];
$total_expense = $conn->query("SELECT SUM(expense_amount) as total FROM expenses")->fetch_assoc()['total'];
$share_per_tenant = $total_expense / ($tenants ?: 1);

// Path to the QR code image in the same directory
$qrImage = "qr.jpg";

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Title
$pdf->Cell(0, 10, 'Monthly Expense Report', 0, 1, 'C');

// Expenses Table
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(120, 10, 'Expense Detail', 1);
$pdf->Cell(70, 10, 'Amount (RM)', 1, 1);
$pdf->SetFont('Arial', '', 12);

while ($row = $expenses->fetch_assoc()) {
    $pdf->Cell(120, 10, $row['expense_detail'], 1);
    $pdf->Cell(70, 10, number_format($row['expense_amount'], 2), 1, 1);
}

// Summary
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Total Expense: RM " . number_format($total_expense, 2), 0, 1);
$pdf->Cell(0, 10, "Total Tenants: " . $tenants, 0, 1);
$pdf->Cell(0, 10, "Share per Tenant: RM " . number_format($share_per_tenant, 2), 0, 1);

// Add QR Code to PDF
$pdf->Ln(20); // Add some spacing
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Make Payment to Mr. Adib using this QR:', 0, 1, 'C');

// Insert the QR code image
$pdf->Image($qrImage, 80, $pdf->GetY(), 50, 50); // Centered QR code

// Output PDF for download
$pdf->Output('D', 'Monthly_Expense_Report.pdf');
?>
