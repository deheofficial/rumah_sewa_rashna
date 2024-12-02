document.addEventListener("DOMContentLoaded", () => {
    fetchData();
});

function fetchData() {
    fetch("api.php")
        .then(response => response.json())
        .then(data => {
            console.log("API Response:", data); // Debugging
            updateTable(data.expenses);
            updateSummary(data.total_tenants, data.total_expense, data.share_per_tenant);
        })
        .catch(error => {
            console.error("Error fetching data:", error);
        });
}

function updateTable(expenses) {
    // Correct ID selector
    const tbody = document.querySelector("#expense-table tbody");
    tbody.innerHTML = ""; // Clear existing rows

    if (expenses.length === 0) {
        tbody.innerHTML = "<tr><td colspan='3'>No expenses found</td></tr>";
        return;
    }

    expenses.forEach(expense => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${expense.expense_detail}</td>
            <td>RM ${parseFloat(expense.expense_amount).toFixed(2)}</td>
            <td><button class="delete-btn" data-id="${expense.id}">Delete</button></td>
        `;
        tbody.appendChild(row);
    });

    // Add delete button functionality
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", event => {
            // Access the expense ID from the data-id attribute of the clicked button
            const id = event.target.getAttribute("data-id"); 
            
            // Show confirmation alert before deleting
            const confirmed = confirm("Are you sure you want to delete this expense?");
            if (confirmed) {
                deleteExpense(id);  // Call the function to delete the expense
            }
        });
    });
}

function updateSummary(totalTenants, totalExpense, sharePerTenant) {
    // Correct ID selectors
    document.getElementById("total-tenants").textContent = totalTenants;
    document.getElementById("total-expense").textContent = `RM ${parseFloat(totalExpense).toFixed(2)}`;
    document.getElementById("share-per-tenant").textContent = `RM ${parseFloat(sharePerTenant).toFixed(2)}`;
}

function deleteExpense(id) {
    fetch('api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded', // Set the correct content type
        },
        body: new URLSearchParams({
            action: 'delete_expense',  // Specify the action for PHP to handle
            expense_id: id  // Send the expense ID for deletion
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            console.log('Expense deleted successfully');
            fetchData();  // Refresh data after deletion
        } else {
            console.error('Error deleting expense:', data.message);
        }
    })
    .catch(error => {
        console.error('Error deleting expense:', error);
    });
}


document.getElementById("view-statistics").addEventListener("click", () => {
    // Show modal
    const modal = document.getElementById("myModal");
    modal.style.display = "block";

    // Fetch data for the chart
    fetch("api.php")
        .then(response => response.json())
        .then(data => {
            const labels = data.expenses.map(exp => exp.expense_detail);
            const amounts = data.expenses.map(exp => parseFloat(exp.expense_amount));

            // Render chart
            const ctx = document.getElementById("pieChart").getContext("2d");
            new Chart(ctx, {
                type: "pie",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            data: amounts,
                            backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56"],
                        },
                    ],
                },
            });
        })
        .catch(error => console.error("Error fetching statistics:", error));
});

// Close modal
document.querySelector(".close").addEventListener("click", () => {
    document.getElementById("myModal").style.display = "none";
});

document.getElementById("generate-pdf").addEventListener("click", () => {
    // Make an AJAX request to generate the PDF
    fetch("generate_pdf.php")
        .then(response => response.blob())  // Handle PDF as a blob response
        .then(pdfBlob => {
            // Create a link element
            const link = document.createElement("a");
            // Create an object URL for the blob
            link.href = URL.createObjectURL(pdfBlob);
            // Set the download attribute to specify the file name
            link.download = "Monthly_Expense_Report.pdf";
            // Trigger a click event to download the PDF
            link.click();
        })
        .catch(error => {
            console.error("Error generating PDF:", error);
        });
});



document.getElementById("add-expense-form").addEventListener("submit", (e) => {
    e.preventDefault(); // Prevent form from refreshing the page

    const expenseDetail = document.getElementById("expense-detail").value;
    const expenseAmount = document.getElementById("expense-amount").value;

    fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=add_expense&expense_detail=${encodeURIComponent(expenseDetail)}&expense_amount=${encodeURIComponent(expenseAmount)}`,
    })
        .then(response => response.json())
        .then(data => {
            console.log("Add Expense Response:", data);
            fetchData(); // Refresh data after adding expense
        })
        .catch(error => console.error("Error adding expense:", error));
});

document.getElementById("add-tenant-form").addEventListener("submit", (e) => {
    e.preventDefault(); // Prevent form from refreshing the page

    const tenantName = document.getElementById("tenant-name").value;

    fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=add_tenant&tenant_name=${encodeURIComponent(tenantName)}`,
    })
        .then(response => response.json())
        .then(data => {
            console.log("Add Tenant Response:", data);
            fetchData(); // Refresh data after adding tenant
        })
        .catch(error => console.error("Error adding tenant:", error));
});


