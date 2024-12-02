<?php
header("Content-Type: application/json");

// Database connection
$conn = new mysqli("localhost", "root", "", "house_expenses");

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_expense':
            $expense_detail = $conn->real_escape_string($_POST['expense_detail']);
            $expense_amount = (float)$_POST['expense_amount'];
            $conn->query("INSERT INTO expenses (expense_detail, expense_amount) VALUES ('$expense_detail', $expense_amount)");
            echo json_encode(['status' => 'success']);
            break;

        case 'add_tenant':
            $tenant_name = $conn->real_escape_string($_POST['tenant_name']);
            $conn->query("INSERT INTO tenants (name) VALUES ('$tenant_name')");
            echo json_encode(['status' => 'success']);
            break;

        case 'delete_expense':
            // Make sure expense_id is passed correctly
            $expense_id = isset($_POST['expense_id']) ? (int)$_POST['expense_id'] : 0;
            if ($expense_id > 0) {
                $conn->query("DELETE FROM expenses WHERE id = $expense_id");
                if ($conn->affected_rows > 0) {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Expense not found or already deleted']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid expense ID']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $expenses = $conn->query("SELECT * FROM expenses")->fetch_all(MYSQLI_ASSOC);
    $tenants = $conn->query("SELECT COUNT(*) AS total_tenants FROM tenants")->fetch_assoc()['total_tenants'];
    $total_expense = $conn->query("SELECT SUM(expense_amount) AS total FROM expenses")->fetch_assoc()['total'] ?? 0;
    $share_per_tenant = $tenants > 0 ? $total_expense / $tenants : 0;

    echo json_encode([
        'expenses' => $expenses,
        'total_tenants' => $tenants,
        'total_expense' => $total_expense,
        'share_per_tenant' => $share_per_tenant
    ]);
}
?>
