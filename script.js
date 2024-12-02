document.addEventListener("DOMContentLoaded", () => {
    const expenseForm = document.getElementById("add-expense-form");
    const tenantForm = document.getElementById("add-tenant-form");
    const expenseTable = document.getElementById("expense-table").querySelector("tbody");
    const totalExpenseEl = document.getElementById("total-expense");
    const totalTenantsEl = document.getElementById("total-tenants");
    const sharePerTenantEl = document.getElementById("share-per-tenant");

    // Fetch and render data
    const fetchData = async () => {
        const response = await fetch("api.php");
        const data = await response.json();

        // Update expenses table
        expenseTable.innerHTML = "";
        data.expenses.forEach(expense => {
            const row = `<tr>
                <td>${expense.expense_detail}</td>
                <td>RM ${parseFloat(expense.expense_amount).toFixed(2)}</td>
                <td><button class="delete-expense" data-id="${expense.id}">Delete</button></td>
            </tr>`;
            expenseTable.innerHTML += row;
        });

        // Update summaries
        totalExpenseEl.innerText = `Total Expense: RM ${parseFloat(data.total_expense).toFixed(2)}`;
        totalTenantsEl.innerText = `Total Tenants: ${data.total_tenants}`;
        sharePerTenantEl.innerText = `Share per Tenant: RM ${parseFloat(data.share_per_tenant).toFixed(2)}`;
    };

    // Add expense
    expenseForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const detail = document.getElementById("expense-detail").value;
        const amount = document.getElementById("expense-amount").value;

        await fetch("api.php", {
            method: "POST",
            body: new URLSearchParams({
                action: "add_expense",
                expense_detail: detail,
                expense_amount: amount
            })
        });

        fetchData(); // Refresh data
    });

    // Add tenant
    tenantForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const name = document.getElementById("tenant-name").value;

        await fetch("api.php", {
            method: "POST",
            body: new URLSearchParams({
                action: "add_tenant",
                tenant_name: name
            })
        });

        fetchData(); // Refresh data
    });

    // Delete expense
    expenseTable.addEventListener("click", async (e) => {
        if (e.target.classList.contains("delete-expense")) {
            const id = e.target.getAttribute("data-id");
            await fetch("api.php", {
                method: "POST",
                body: new URLSearchParams({
                    action: "delete_expense",
                    expense_id: id
                })
            });

            fetchData(); // Refresh data
        }
    });

    fetchData(); // Initial load
});
