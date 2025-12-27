<?php
session_start();
include '../config.php';

$page_title = "Manage Proposals";
include 'header.php';

// ---------------- AUTH CHECK ----------------
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$proposal_success = "";
$proposal_error = "";

// Status labels
$status_labels = [
    'pending' => 'Pending',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected'
];

// ---------------- DELETE PROPOSAL ----------------
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $proposal_id = (int)$_GET['id'];

    $del = $conn->prepare("DELETE FROM proposals WHERE proposal_id = ?");
    $del->bind_param("i", $proposal_id);

    if ($del->execute()) {
        $proposal_success = "Proposal deleted successfully.";
    } else {
        $proposal_error = "Failed to delete proposal.";
    }
}

// ---------------- LOAD EDIT FORM ----------------
$edit_record = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $proposal_id = (int)$_GET['id'];

    $stmt = $conn->prepare("
        SELECT p.*, u.username AS freelancer_name, j.title AS job_title
        FROM proposals p
        JOIN users u ON p.freelancer_id = u.user_id
        JOIN jobs j ON p.job_id = j.jobs_id
        WHERE proposal_id = ?
    ");
    $stmt->bind_param("i", $proposal_id);
    $stmt->execute();
    $edit_record = $stmt->get_result()->fetch_assoc();
}

// ---------------- UPDATE PROPOSAL ----------------
if (isset($_POST['update_proposal'])) {

    $proposal_id = (int)$_POST['proposal_id'];
    $cover_letter = trim($_POST['cover_letter']);
    $bid_amount = trim($_POST['bid_amount']);
    $status = trim($_POST['status']);

    if ($cover_letter === '' || $bid_amount === '') {
        $proposal_error = "Please fill in all fields.";
    } else {

        $upd = $conn->prepare("
            UPDATE proposals 
            SET cover_letter = ?, bid_amount = ?, status = ? 
            WHERE proposal_id = ?
        ");
        $upd->bind_param("sssi", $cover_letter, $bid_amount, $status, $proposal_id);

        if ($upd->execute()) {
            header("Location: manage_proposals.php?success=1");
            exit();
        } else {
            $proposal_error = "Failed to update proposal.";
        }
    }
}

if (isset($_GET['success'])) {
    $proposal_success = "Proposal updated successfully.";
}
?>

<main class="p-8">

    <h2 class="text-2xl font-semibold mb-6">Manage Proposals</h2>

    <?php if ($proposal_success): ?>
        <div id="proposalSuccessMsg" class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $proposal_success ?></div>
    <?php endif; ?>

    <?php if ($proposal_error): ?>
        <div id="proposalErrorMsg" class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $proposal_error ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && $edit_record): ?>

        <!-- EDIT FORM -->
        <form method="POST" class="bg-white shadow rounded-xl p-6 max-w-3xl space-y-6 validateForm">

            <input type="hidden" name="proposal_id" value="<?= $edit_record['proposal_id'] ?>">

            <div>
                <label class="block text-gray-700 font-medium mb-1">Freelancer</label>
                <input type="text" value="<?= htmlspecialchars($edit_record['freelancer_name']) ?>" class="w-full border px-3 py-2 rounded" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Job</label>
                <input type="text" value="<?= htmlspecialchars($edit_record['job_title']) ?>" class="w-full border px-3 py-2 rounded" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Cover Letter</label>
                <textarea
                    name="cover_letter"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required"><?= htmlspecialchars($edit_record['cover_letter']) ?></textarea>
                <p class="error text-red-500 text-sm mt-1" id="cover_letterError"></p>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Bid Amount</label>
                <input
                    type="text"
                    name="bid_amount"
                    value="<?= $edit_record['bid_amount'] ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required numeric">
                <p class="error text-red-500 text-sm mt-1" id="bid_amountError"></p>
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
                    Update Proposal
                </button>

                <a href="manage_proposals.php" class="text-indigo-600 hover:underline">Back</a>
            </div>

            <input type="hidden" name="update_proposal" value="1">
        </form>

    <?php else: ?>

        <!-- PROPOSALS TABLE -->
        <?php
        $proposals = $conn->query("
            SELECT p.*, u.username AS freelancer_name, j.title AS job_title
            FROM proposals p
            JOIN users u ON p.freelancer_id = u.user_id
            JOIN jobs j ON p.job_id = j.jobs_id
            ORDER BY p.proposal_id DESC
        ");
        ?>

        <div class="bg-white shadow rounded-xl p-6">
            <table class="min-w-full divide-y divide-gray-200 text-center">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm">
                    <tr>
                        <th class="px-4 py-3">Freelancer</th>
                        <th class="px-4 py-3">Job</th>
                        <th class="px-4 py-3">Bid Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Submitted At</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $proposals->fetch_assoc()): ?>
                        <tr>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['freelancer_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['job_title']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($row['bid_amount']) ?></td>
                            <td class="px-4 py-3"><?= $status_labels[$row['status']] ?? $row['status'] ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= date("d M Y", strtotime($row['created_at'])) ?></td>
                            <td class="px-4 py-3 space-x-4">
                                <a href="manage_proposals.php?action=edit&id=<?= $row['proposal_id'] ?>" class="text-indigo-600 hover:underline">Edit</a>
                                <a href="manage_proposals.php?action=delete&id=<?= $row['proposal_id'] ?>" onclick="return confirm('Delete this proposal?')" class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

    <script>
        // Auto-hide success message after 2 seconds
        setTimeout(function() {
            const msg = document.getElementById('proposalSuccessMsg');
            if (msg) {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    msg.remove();
                }, 500);
            }

            const err = document.getElementById('proposalErrorMsg');
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