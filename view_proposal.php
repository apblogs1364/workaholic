<?php
session_start();
include 'config.php';

// Only logged-in freelancers can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'freelancer') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$proposal_success = "";
$proposal_error = "";
$status_labels = ['pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected'];

// Handle delete action
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $proposal_id = (int)$_GET['id'];
    $chk = $conn->prepare("SELECT proposal_id FROM proposals WHERE proposal_id=? AND freelancer_id=?");
    $chk->bind_param("ii", $proposal_id, $user_id);
    $chk->execute();
    $own = $chk->get_result()->fetch_assoc();
    if ($own) {
        $del = $conn->prepare("DELETE FROM proposals WHERE proposal_id=?");
        $del->bind_param("i", $proposal_id);
        if ($del->execute()) $proposal_success = "Proposal deleted successfully at " . date("Y-m-d H:i:s");
        else $proposal_error = "Failed to delete proposal.";
    } else $proposal_error = "Invalid proposal or permission denied.";
}

// Handle update
$edit_proposal = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_proposal'])) {
    $proposal_id = (int)($_POST['proposal_id'] ?? 0);
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $bid_amount = trim($_POST['bid_amount'] ?? '');

    if ($cover_letter === '' || $bid_amount === '') $proposal_error = "Please fill all required fields.";
    else {
        $chk = $conn->prepare("SELECT proposal_id FROM proposals WHERE proposal_id=? AND freelancer_id=?");
        $chk->bind_param("ii", $proposal_id, $user_id);
        $chk->execute();
        $own = $chk->get_result()->fetch_assoc();
        if ($own) {
            $upd = $conn->prepare("UPDATE proposals SET cover_letter=?, bid_amount=? WHERE proposal_id=?");
            $upd->bind_param("sdi", $cover_letter, $bid_amount, $proposal_id);
            if ($upd->execute()) {
                header("Location: view_proposal.php?success=1");
                exit();
            } else $proposal_error = "Failed to update proposal.";
        } else $proposal_error = "Invalid proposal or permission denied.";
    }
}

// Load edit proposal
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $proposal_id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT p.*, j.title AS job_title FROM proposals p JOIN jobs j ON j.jobs_id=p.job_id WHERE proposal_id=? AND freelancer_id=?");
    $stmt->bind_param("ii", $proposal_id, $user_id);
    $stmt->execute();
    $edit_proposal = $stmt->get_result()->fetch_assoc();
}

// Fetch all proposals
$stmt = $conn->prepare("SELECT p.*, j.title AS job_title FROM proposals p JOIN jobs j ON j.jobs_id=p.job_id WHERE freelancer_id=? ORDER BY p.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$proposals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "View/Edit Proposals";
include 'header.php';
?>

<!-- CSS same as proposal.php -->
<style>
    .form-control {
        border: 1px solid #d1d5db;
        border-radius: .375rem;
        padding: .6rem .85rem;
        width: 100%;
        font-size: .95rem;
        color: #111827;
        background: #fff;
        transition: .2s;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, .3);
        outline: none;
    }

    label {
        font-size: .9rem;
        color: #374151;
    }

    .error {
        color: #ef4444;
        font-size: .875rem;
        margin-top: .25rem;
        line-height: 1.25;
    }
</style>

<div class="max-w-5xl mx-auto mt-12 mb-12">
    <div class="bg-white shadow-xl rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Your Proposals</h1>

        <?php if ($proposal_success || isset($_GET['success'])): ?>
            <div id="proposalSuccessMsg" class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">
                <?= htmlspecialchars($proposal_success ?: "Proposal updated successfully at " . date("Y-m-d H:i:s")); ?>
            </div>
        <?php endif; ?>
        <?php if ($proposal_error): ?>
            <div id="proposalSuccessMsg" class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($proposal_error) ?></div>
        <?php endif; ?>

        <!-- Proposals Table -->
        <?php if (empty($proposals)): ?>
            <p class="text-gray-600 mb-6">You have not submitted any proposals yet.</p>
        <?php else: ?>
            <div class="overflow-x-auto mb-8">
                <table class="min-w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 border-b text-left">Job</th>
                            <th class="px-4 py-2 border-b text-center">Bid Amount</th>
                            <th class="px-4 py-2 border-b text-center">Status</th>
                            <th class="px-4 py-2 border-b text-center">Submitted On</th>
                            <th class="px-4 py-2 border-b text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($proposals as $p): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b"><?= htmlspecialchars($p['job_title']); ?></td>
                                <td class="px-4 py-2 border-b text-center"><?= htmlspecialchars($p['bid_amount']); ?></td>
                                <td class="px-4 py-2 border-b text-center"><?= $status_labels[$p['status']] ?? ucfirst($p['status']); ?></td>
                                <td class="px-4 py-2 border-b text-center"><?= htmlspecialchars($p['created_at']); ?></td>
                                <td class="px-4 py-2 border-b text-center space-x-2">
                                    <a href="view_proposal.php?action=edit&id=<?= (int)$p['proposal_id']; ?>" class="text-indigo-600 hover:underline">Edit</a>
                                    <a href="view_proposal.php?action=delete&id=<?= (int)$p['proposal_id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this proposal?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Edit Proposal Form -->
        <?php if ($edit_proposal): ?>
            <h2 class="text-xl font-semibold text-gray-800 mb-3">Edit Proposal</h2>
            <form method="POST" id="proposalForm">
                <input type="hidden" name="proposal_id" value="<?= (int)$edit_proposal['proposal_id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="mb-1 block">Job</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($edit_proposal['job_title']); ?>" readonly>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block">Cover Letter</label>
                        <textarea name="cover_letter" rows="3" class="form-control" data-validation="required"><?= htmlspecialchars($edit_proposal['cover_letter']); ?></textarea>
                        <div class="error" id="cover_letterError"></div>
                    </div>
                    <div>
                        <label class="mb-1 block">Bid Amount</label>
                        <input type="text" name="bid_amount" value="<?= htmlspecialchars($edit_proposal['bid_amount']); ?>" class="form-control" data-validation="required numeric">
                        <div class="error" id="bid_amountError"></div>
                    </div>
                    <div>
                        <label class="mb-1 block">Status</label>
                        <input type="text" class="form-control" value="<?= $status_labels[$edit_proposal['status']] ?? $edit_proposal['status']; ?>" readonly>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" name="save_proposal" class="px-8 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 shadow">
                        Update Proposal
                    </button>
                    <a href="view_proposal.php" class="ml-3 px-6 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 shadow inline-block text-center">Cancel Edit</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="assets/jquery.min.js"></script>
<script src="assets/validate.js"></script>
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
    }, 2000);
</script>

<?php include 'footer.php'; ?>