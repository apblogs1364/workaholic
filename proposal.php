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

// Get job_id from URL
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
if ($job_id <= 0) {
    die("Invalid job selected.");
}

// Handle insert form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_proposal'])) {
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $bid_amount = trim($_POST['bid_amount'] ?? '');

    if ($cover_letter === '' || $bid_amount === '') {
        $proposal_error = "Please fill in all required fields.";
    } else {
        $ins = $conn->prepare("INSERT INTO proposals (job_id, freelancer_id, cover_letter, bid_amount, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $ins->bind_param("iisd", $job_id, $user_id, $cover_letter, $bid_amount);
        if ($ins->execute()) {
            $proposal_success = "Proposal submitted successfully at " . date("Y-m-d H:i:s");
        } else {
            $proposal_error = "Failed to submit proposal.";
        }
    }
}

// Fetch only the job that matches the job_id
$stmt = $conn->prepare("
    SELECT j.jobs_id, j.title, j.description, j.budget, c.company_name
    FROM jobs j
    JOIN companies c ON j.company_id = c.company_id
    WHERE j.jobs_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job_result = $stmt->get_result();
if ($job_result->num_rows === 0) {
    die("Job not found.");
}
$job = $job_result->fetch_assoc();

$page_title = "Submit Proposal";
include 'header.php';
?>

<!-- Proposal Form CSS -->
<style>
    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.6rem 0.85rem;
        width: 100%;
        font-size: 0.95rem;
        color: #111827;
        background-color: #ffffff;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        outline: none;
    }

    label {
        font-size: 0.9rem;
        color: #374151;
    }

    .error {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        line-height: 1.25;
    }

    .job-card {
        border: 1px solid #e5e7eb;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        background-color: #ffffff;
    }
</style>

<div class="max-w-3xl mx-auto mt-12 mb-12">
    <div class="bg-white shadow-xl rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Submit Proposal</h1>

        <?php if ($proposal_success): ?>
            <div id="proposalSuccessMsg" class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($proposal_success) ?></div>
        <?php endif; ?>

        <?php if ($proposal_error): ?>
            <div id="proposalErrorMsg" class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($proposal_error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="job-card">
                <h3 class="text-lg font-semibold mb-1"><?= htmlspecialchars($job['title']); ?></h3>
                <p class="text-gray-700 text-sm mb-1"><strong>Company:</strong> <?= htmlspecialchars($job['company_name']); ?></p>
                <p class="text-gray-700 text-sm mb-1"><strong>Description:</strong> <?= htmlspecialchars($job['description']); ?></p>
                <p class="text-gray-700 text-sm mb-2"><strong>Budget:</strong> <?= htmlspecialchars($job['budget']); ?></p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="mb-1 block">Cover Letter</label>
                        <textarea name="cover_letter" rows="3" class="form-control" required></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block">Bid Amount</label>
                        <input type="text" name="bid_amount" class="form-control" required>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="save_proposal" class="px-6 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Submit Proposal
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

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

<script src="assets/jquery.min.js"></script>
<script src="assets/validate.js"></script>

<?php include 'footer.php'; ?>