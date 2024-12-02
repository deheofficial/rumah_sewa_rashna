<?php
require('fpdf/fpdf.php');
$conn = new mysqli("localhost", "root", "", "house_expenses");

// Fetch data
$expenses = $conn->query("SELECT * FROM expenses");
$tenants = $conn->query("SELECT COUNT(*) as total_tenants FROM tenants")->fetch_assoc()['total_tenants'];
$total_expense = $conn->query("SELECT SUM(expense_amount) as total FROM expenses")->fetch_assoc()['total'];
$share_per_tenant = $total_expense / ($tenants ?: 1);

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

// Output
$pdf->Output('D', 'Monthly_Expense_Report.pdf');
?>
