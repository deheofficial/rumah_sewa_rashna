<?php
header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "house_expenses");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add_expense':
            $expense_detail = $_POST['expense_detail'];
            $expense_amount = $_POST['expense_amount'];
            $conn->query("INSERT INTO expenses (expense_detail, expense_amount) VALUES ('$expense_detail', '$expense_amount')");
            echo json_encode(['status' => 'success']);
            break;

        case 'add_tenant':
            $tenant_name = $_POST['tenant_name'];
            $conn->query("INSERT INTO tenants (name) VALUES ('$tenant_name')");
            echo json_encode(['status' => 'success']);
            break;

        case 'delete_expense':
            $expense_id = $_POST['expense_id'];
            $conn->query("DELETE FROM expenses WHERE id = $expense_id");
            echo json_encode(['status' => 'success']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $expenses = $conn->query("SELECT * FROM expenses")->fetch_all(MYSQLI_ASSOC);
    $tenants = $conn->query("SELECT COUNT(*) as total_tenants FROM tenants")->fetch_assoc()['total_tenants'];
    $total_expense = $conn->query("SELECT SUM(expense_amount) as total FROM expenses")->fetch_assoc()['total'];
    $share_per_tenant = $total_expense / ($tenants ?: 1);

    echo json_encode([
        'expenses' => $expenses,
        'total_tenants' => $tenants,
        'total_expense' => $total_expense,
        'share_per_tenant' => $share_per_tenant
    ]);
}
