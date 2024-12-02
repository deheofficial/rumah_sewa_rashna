<?php
$conn = new mysqli("localhost", "root", "", "house_expenses");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_expense'])) {
        $expense_detail = $_POST['expense_detail'];
        $expense_amount = $_POST['expense_amount'];
        $conn->query("INSERT INTO expenses (expense_detail, expense_amount) VALUES ('$expense_detail', '$expense_amount')");
    } elseif (isset($_POST['add_tenant'])) {
        $tenant_name = $_POST['tenant_name'];
        $conn->query("INSERT INTO tenants (name) VALUES ('$tenant_name')");
    } elseif (isset($_POST['delete_expense'])) {
        $expense_id = $_POST['expense_id'];
        $conn->query("DELETE FROM expenses WHERE id = $expense_id");
    }
}

// Fetch data
$expenses = $conn->query("SELECT * FROM expenses");
$tenants = $conn->query("SELECT COUNT(*) as total_tenants FROM tenants")->fetch_assoc()['total_tenants'];
$total_expense = $conn->query("SELECT SUM(expense_amount) as total FROM expenses")->fetch_assoc()['total'];

$share_per_tenant = $total_expense / ($tenants ?: 1);

// Fetch all expense details and amounts
$expense_details = [];
$expense_amounts = [];
$expenses_query = $conn->query("SELECT expense_detail, expense_amount FROM expenses");
while ($row = $expenses_query->fetch_assoc()) {
    $expense_details[] = $row['expense_detail'];
    $expense_amounts[] = (float)$row['expense_amount'];
}

// Convert arrays to JSON for use in JavaScript
$expense_details_json = json_encode($expense_details);
$expense_amounts_json = json_encode($expense_amounts);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Expenses</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js CDN -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }
        header {
            background-color: black;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }
        h1, h3 {
            color: white;
        }
        form {
            background-color: grey;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px auto;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="number"] {
            width: 95%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: black;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f9;
            color: #333;
        }
        p {
            width: 90%;
            margin: 10px auto;
            font-size: 1.1em;
            color: #333;
        }
        footer {
            text-align: center;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        .trash-icon {
            color: red;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }
        .trash-icon:hover {
            color: darkred;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>No.3 Rashna Expense System</h1>
    </header>

    <!-- Add Expense Form -->
    <form method="POST">
        <h3>Add Expense</h3>
        <input type="text" name="expense_detail" placeholder="Expense Detail" required>
        <input type="number" name="expense_amount" placeholder="Amount" step="0.01" required>
        <button type="submit" name="add_expense">Add Expense</button>
    </form>

    <!-- Add Tenant Form -->
    <form method="POST">
        <h3>Add Tenant</h3>
        <input type="text" name="tenant_name" placeholder="Tenant Name" required>
        <button type="submit" name="add_tenant">Add Tenant</button>
    </form>

    <!-- Expense Table -->
    <h3 style="text-align: center;">Expense Summary</h3>
    <table>
        <thead>
            <tr>
                <th>Expense Detail</th>
                <th>Amount (RM)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $expenses->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['expense_detail'] ?></td>
                    <td><?= number_format($row['expense_amount'], 2) ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="expense_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_expense" class="trash-icon" onclick="return confirm('Are you sure you want to delete this expense?');">
                                <i class="fas fa-trash-alt"></i> <!-- Font Awesome Trash Icon -->
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <p><strong>Total Expense: RM <?= number_format($total_expense, 2) ?></strong></p>
    <p><strong>Total Tenants: <?= $tenants ?></strong></p>
    <p><strong>Share per Tenant: RM <?= number_format($share_per_tenant, 2) ?></strong></p>

    <!-- Modal for Pie Chart -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Expense Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <!-- JavaScript -->
    <script>

    function openModal() {
        document.getElementById("myModal").style.display = "block";
        renderChart(); // Render the pie chart when the modal is opened
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById("myModal").style.display = "none";
    }
    // Render Pie Chart using Chart.js
    function renderChart() {
        const ctx = document.getElementById("pieChart").getContext("2d");

        // Get the expense details and amounts from PHP
        const expenseDetails = <?php echo $expense_details_json; ?>;
        const expenseAmounts = <?php echo $expense_amounts_json; ?>;

        // Create the chart data
        const chartData = {
            labels: expenseDetails,
            datasets: [{
                label: 'Expense Distribution',
                data: expenseAmounts,
                backgroundColor: generateColors(expenseDetails.length),  // Generate unique colors for each expense
                borderColor: generateColors(expenseDetails.length),
                borderWidth: 1
            }]
        };

        // Create the pie chart
        new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': RM ' + tooltipItem.raw.toFixed(2);
                            }
                        }
                    }
                }
            } // Closing brace for options
        }); // Closing parenthesis for new Chart
    }

    // Function to generate random colors for the chart slices
    function generateColors(num) {
        const colors = [];
        const colorPalette = ['#FF6347', '#36A2EB', '#FFCD02', '#4CAF50', '#8E44AD', '#FF5733', '#F39C12', '#9B59B6', '#1ABC9C', '#E74C3C'];
        for (let i = 0; i < num; i++) {
            colors.push(colorPalette[i % colorPalette.length]);  // Cycle through predefined colors
        }
        return colors;
    }
</script>


<div class="card" style="margin: 20px auto; padding: 20px; width: 80%; max-width: 400px; background-color: #ffffff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
    <!-- View Expense Statistics Button -->
    <button onclick="openModal()" style="display: block; margin: 10px auto; background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s;">
        View Expense Statistics
    </button>

    <!-- Generate PDF Button -->
    <form method="POST" action="generate_pdf.php">
        <button type="submit" name="generate_pdf" style="display: block; margin: 10px auto; background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s;">
            Generate PDF
        </button>
    </form>
</div>

    


</body>
</html>
