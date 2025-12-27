<?php
session_start();
include '../config.php';

$page_title = "Manage Contracts";
include 'header.php';

// ---------------- AUTH CHECK ----------------
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$contract_success = "";
$contract_error = "";

// Status labels
$status_labels = [
    'active' => 'Active',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled'
];

// ---------------- DELETE CONTRACT ----------------
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $contract_id = (int)$_GET['id'];

    $del = $conn->prepare("DELETE FROM contracts WHERE contract_id = ?");
    $del->bind_param("i", $contract_id);

    if ($del->execute()) {
        $contract_success = "Contract deleted successfully.";
    } else {
        $contract_error = "Failed to delete contract.";
    }
}

// ---------------- LOAD EDIT FORM ----------------
$edit_record = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $contract_id = (int)$_GET['id'];

    $stmt = $conn->prepare("
        SELECT con.*, j.title AS job_title, 
               f.fname AS freelancer_fname, f.lname AS freelancer_lname, 
               u.username AS freelancer_name, 
               c.company_name, cu.username AS company_username
        FROM contracts con
        JOIN jobs j ON con.job_id = j.jobs_id
        JOIN freelancers f ON con.freelancer_id = f.freelancer_id
        JOIN users u ON f.user_id = u.user_id
        JOIN companies c ON con.company_id = c.company_id
        JOIN users cu ON c.user_id = cu.user_id
        WHERE con.contract_id = ?
    ");
    $stmt->bind_param("i", $contract_id);
    $stmt->execute();
    $edit_record = $stmt->get_result()->fetch_assoc();
}

// ---------------- UPDATE CONTRACT ----------------
if (isset($_POST['update_contract'])) {

    $contract_id = (int)$_POST['contract_id'];
    $agreed_amount = trim($_POST['agreed_amount']);
    $status = trim($_POST['status']);

    if ($agreed_amount === '') {
        $contract_error = "Please fill in all fields.";
    } else {

        $upd = $conn->prepare("
            UPDATE contracts 
            SET agreed_amount = ?, status = ? 
            WHERE contract_id = ?
        ");
        $upd->bind_param("dsi", $agreed_amount, $status, $contract_id);

        if ($upd->execute()) {
            header("Location: manage_contracts.php?success=1");
            exit();
        } else {
            $contract_error = "Failed to update contract.";
        }
    }
}

if (isset($_GET['success'])) {
    $contract_success = "Contract updated successfully.";
}
?>

<main class="p-8">

    <h2 class="text-2xl font-semibold mb-6">Manage Contracts</h2>

    <?php if ($contract_success): ?>
        <div id="contractSuccessMsg" class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $contract_success ?></div>
    <?php endif; ?>

    <?php if ($contract_error): ?>
        <div id="contractErrorMsg" class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $contract_error ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && $edit_record): ?>

        <!-- EDIT FORM -->
        <form method="POST" class="bg-white shadow rounded-xl p-6 max-w-3xl space-y-6 validateForm">

            <input type="hidden" name="contract_id" value="<?= $edit_record['contract_id'] ?>">

            <div>
                <label class="block text-gray-700 font-medium mb-1">Freelancer</label>
                <input type="text" value="<?= htmlspecialchars($edit_record['freelancer_name']) ?>" class="w-full border px-3 py-2 rounded" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Company</label>
                <input type="text" value="<?= htmlspecialchars($edit_record['company_name']) ?>" class="w-full border px-3 py-2 rounded" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Job</label>
                <input type="text" value="<?= htmlspecialchars($edit_record['job_title']) ?>" class="w-full border px-3 py-2 rounded" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Agreed Amount</label>
                <input
                    type="text"
                    name="agreed_amount"
                    value="<?= $edit_record['agreed_amount'] ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required numeric">
                <p class="error text-red-500 text-sm mt-1" id="agreed_amountError"></p>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Status</label>
                <select name="status" class="w-full border px-3 py-2 rounded">
                    <?php foreach ($status_labels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $edit_record['status'] === $k ? 'selected' : '' ?>>
                            <?= $v ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-center gap-4 pt-3">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">
                    Update Contract
                </button>

                <a href="manage_contracts.php" class="text-indigo-600 hover:underline">Back</a>
            </div>

            <input type="hidden" name="update_contract" value="1">
        </form>

    <?php else: ?>

        <!-- CONTRACTS TABLE -->
        <?php
        $contracts = $conn->query("
            SELECT con.*, j.title AS job_title, 
                   f.fname AS freelancer_fname, f.lname AS freelancer_lname, 
                   u.username AS freelancer_name, 
                   c.company_name
            FROM contracts con
            JOIN jobs j ON con.job_id = j.jobs_id
            JOIN freelancers f ON con.freelancer_id = f.freelancer_id
            JOIN users u ON f.user_id = u.user_id
            JOIN companies c ON con.company_id = c.company_id
            ORDER BY con.contract_id DESC
        ");
        ?>

        <div class="bg-white shadow rounded-xl p-6">
            <table class="min-w-full divide-y divide-gray-200 text-center">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                    <tr>
                        <th class="px-4 py-3">Freelancer</th>
                        <th class="px-4 py-3">Company</th>
                        <th class="px-4 py-3">Job</th>
                        <th class="px-4 py-3">Agreed Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $contracts->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['freelancer_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['company_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['job_title']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['agreed_amount']) ?></td>
                            <td class="px-4 py-3"><?= $status_labels[$row['status']] ?? $row['status'] ?></td>
                            <td class="px-4 py-3 space-x-4">
                                <a href="manage_contracts.php?action=edit&id=<?= $row['contract_id'] ?>" class="text-indigo-600 hover:underline">Edit</a>
                                <a href="manage_contracts.php?action=delete&id=<?= $row['contract_id'] ?>" onclick="return confirm('Delete this contract?')" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

    <script>
        // Auto-hide success/error message after 2 seconds
        setTimeout(function() {
            const msg = document.getElementById('contractSuccessMsg');
            if (msg) {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            }

            const err = document.getElementById('contractErrorMsg');
            if (err) {
                err.style.opacity = '0';
                err.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    err.remove();
                }, 500);
            }
        }, 2000);
    </script>

    <script src="../assets/jquery.min.js"></script>
    <script src="../assets/validate.js"></script>

</main>

<?php include 'footer.php'; ?>