<?php
session_start();
include 'config.php';

// Only logged-in companies can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$job_success = "";
$job_error = "";
$status_labels = [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'close' => 'Close'
];

// Find this user's company_id
$stmt = $conn->prepare("SELECT company_id, business_type AS category_id FROM companies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
    $job_error = "Company profile not found. Please complete your company profile first.";
    $company_id = null;
    $company_category_id = null;
} else {
    $company_id = (int)$res['company_id'];
    $company_category_id = (int)$res['category_id'];  // Auto-selected category
}

// Handle delete action
if ($company_id && isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $job_id = (int)$_GET['id'];

    // Verify job belongs to this company
    $chk = $conn->prepare("SELECT jobs_id FROM jobs WHERE jobs_id = ? AND company_id = ?");
    $chk->bind_param("ii", $job_id, $company_id);
    $chk->execute();
    $own = $chk->get_result()->fetch_assoc();

    if ($own) {
        // Delete proposals first (optional but cleaner)
        $delP = $conn->prepare("DELETE FROM proposals WHERE job_id = ?");
        $delP->bind_param("i", $job_id);
        $delP->execute();

        // Delete job
        $delJ = $conn->prepare("DELETE FROM jobs WHERE jobs_id = ?");
        $delJ->bind_param("i", $job_id);
        if ($delJ->execute()) {
            $job_success = "Job deleted successfully at " . date("Y-m-d H:i:s");
        } else {
            $job_error = "Failed to delete job.";
        }
    } else {
        $job_error = "Invalid job or permission denied.";
    }
}

// Handle create/update form submit
$edit_job = null;
if ($company_id && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_job'])) {
    $job_title = trim($_POST['job_title'] ?? '');
    $job_description = trim($_POST['job_description'] ?? '');
    $job_budget = trim($_POST['job_budget'] ?? '');
    $job_status = trim($_POST['job_status'] ?? 'open');
    $job_category_id = (int)($_POST['job_category_id'] ?? 0);
    $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;

    if ($job_title === '' || $job_description === '' || $job_budget === '') {
        $job_error = "Please fill in all required fields.";
    } else {
        if ($job_id > 0) {
            // UPDATE (edit mode) – verify ownership
            $chk = $conn->prepare("SELECT jobs_id FROM jobs WHERE jobs_id = ? AND company_id = ?");
            $chk->bind_param("ii", $job_id, $company_id);
            $chk->execute();
            $own = $chk->get_result()->fetch_assoc();

            if ($own) {
                $upd = $conn->prepare("UPDATE jobs SET category_id = ?, title = ?, description = ?, budget = ?, status = ? WHERE jobs_id = ?");
                $upd->bind_param("issssi", $company_category_id, $job_title, $job_description, $job_budget, $job_status, $job_id);
                if ($upd->execute()) {
                    $job_success = "Job updated successfully at " . date("Y-m-d H:i:s");
                    header("Location: manage_job.php?success=1");
                    exit();
                } else {
                    $job_error = "Failed to update job.";
                }
            } else {
                $job_error = "Invalid job or permission denied.";
            }
        } else {
            // INSERT (create mode)
            $ins = $conn->prepare("INSERT INTO jobs (company_id, category_id, title, description, budget, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $ins->bind_param("iissss", $company_id, $company_category_id, $job_title, $job_description, $job_budget, $job_status);
            if ($ins->execute()) {
                $job_success = "Job created successfully at " . date("Y-m-d H:i:s");
            } else {
                $job_error = "Failed to create job.";
            }
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $job_success = "Job updated successfully at " . date("Y-m-d H:i:s");
}

// If edit action, load job into $edit_job
if ($company_id && isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $job_id = (int)$_GET['id'];
    $chk = $conn->prepare("SELECT * FROM jobs WHERE jobs_id = ? AND company_id = ?");
    $chk->bind_param("ii", $job_id, $company_id);
    $chk->execute();
    $edit_job = $chk->get_result()->fetch_assoc();
    if (!$edit_job) {
        $job_error = "Invalid job or permission denied.";
    }
}

// Fetch all jobs for this company with proposal count
$company_jobs = [];
if ($company_id) {
    $sqlJobs = "
        SELECT j.jobs_id, j.title, j.status, j.budget, j.created_at,
               COUNT(p.proposal_id) AS proposal_count
        FROM jobs j
        LEFT JOIN proposals p ON p.job_id = j.jobs_id
        WHERE j.company_id = ?
        GROUP BY j.jobs_id, j.title, j.status, j.budget, j.created_at
        ORDER BY j.created_at DESC
    ";
    $stmtJobs = $conn->prepare($sqlJobs);
    $stmtJobs->bind_param("i", $company_id);
    $stmtJobs->execute();
    $company_jobs = $stmtJobs->get_result()->fetch_all(MYSQLI_ASSOC);
}

$page_title = "Manage Jobs";
include 'header.php';
?>

<style>
    .error {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        line-height: 1.25;
    }

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
</style>

<div class="max-w-5xl mx-auto mt-12 mb-12">
    <div class="bg-white shadow-xl rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Manage Jobs</h1>

        <?php if ($job_success): ?>
            <div id="jobSuccessMsg" class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">
                <?php echo htmlspecialchars($job_success); ?>
            </div>
        <?php endif; ?>

        <?php if ($job_error): ?>
            <div class="mb-4 px-4 py-2 bg-red-100 text-red-800 rounded">
                <?php echo htmlspecialchars($job_error); ?>
            </div>
        <?php endif; ?>

        <!-- Jobs list -->
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Your Job Posts</h2>

        <?php if (!$company_id): ?>
            <p class="text-gray-600">Please complete your company profile before posting jobs.</p>
        <?php else: ?>
            <?php if (empty($company_jobs)): ?>
                <p class="text-gray-600 mb-6">You have not posted any jobs yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto mb-8">
                    <table class="min-w-full border border-gray-200 text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 border-b text-left">Title</th>
                                <th class="px-4 py-2 border-b text-center">Proposals</th>
                                <th class="px-4 py-2 border-b text-center">Budget</th>
                                <th class="px-4 py-2 border-b text-center">Status</th>
                                <th class="px-4 py-2 border-b text-center">Posted On</th>
                                <th class="px-4 py-2 border-b text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($company_jobs as $job): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 border-b">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        <?php echo (int)$job['proposal_count']; ?>
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        <?php echo htmlspecialchars($job['budget']); ?>
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        <?php
                                        $status = $job['status'];
                                        echo $status_labels[$status] ?? ucfirst($status);
                                        ?>
                                    </td>
                                    <td class="px-4 py-2 border-b text-center">
                                        <?php echo htmlspecialchars($job['created_at']); ?>
                                    </td>
                                    <td class="px-4 py-2 border-b text-center space-x-2">
                                        <a href="manage_job.php?action=edit&id=<?php echo (int)$job['jobs_id']; ?>"
                                            class="text-indigo-600 hover:underline">Edit</a>
                                        <a href="manage_job.php?action=delete&id=<?php echo (int)$job['jobs_id']; ?>"
                                            class="text-red-600 hover:underline"
                                            onclick="return confirm('Are you sure you want to delete this job?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Job form (create/edit) -->
            <h2 class="text-xl font-semibold text-gray-800 mb-3">
                <?php echo $edit_job ? 'Edit Job' : 'Post New Job'; ?>
            </h2>

            <form method="POST">
                <?php if ($edit_job): ?>
                    <input type="hidden" name="job_id" value="<?php echo (int)$edit_job['jobs_id']; ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 md-grid-cols-2 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="mb-1 block">Job Title</label>
                        <input type="text" name="job_title"
                            value="<?php echo htmlspecialchars($edit_job['title'] ?? ''); ?>"
                            class="form-control" data-validation="required alpha">
                        <div class="error" id="job_titleError"></div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block">Description</label>
                        <textarea name="job_description" rows="3" class="form-control" data-validation="required"><?php echo htmlspecialchars($edit_job['description'] ?? ''); ?></textarea>
                        <div class="error" id="job_descriptionError"></div>
                    </div>

                    <div>
                        <label class="mb-1 block">Budget</label>
                        <input type="text" name="job_budget"
                            value="<?php echo htmlspecialchars($edit_job['budget'] ?? ''); ?>"
                            class="form-control" data-validation="required numeric">
                        <div class="error" id="job_budgetError"></div>
                    </div>

                    <div>
                        <label class="mb-1 block">Status</label>
                        <select name="job_status" class="form-control">
                            <?php $currentStatus = $edit_job['status'] ?? 'open'; ?>
                            <option value="open" <?php echo $currentStatus === 'open' ? 'selected' : ''; ?>>
                                Open
                            </option>
                            <option value="in_progress" <?php echo $currentStatus === 'in_progress' ? 'selected' : ''; ?>>
                                In Progress
                            </option>
                            <option value="completed" <?php echo $currentStatus === 'completed' ? 'selected' : ''; ?>>
                                Completed
                            </option>
                            <option value="close" <?php echo $currentStatus === 'close' ? 'selected' : ''; ?>>
                                Close
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" name="save_job"
                        class="px-8 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 shadow">
                        <?php echo $edit_job ? 'Update Job' : 'Create Job'; ?>
                    </button>
                    <?php if ($edit_job): ?>
                        <a href="manage_job.php"
                            class="ml-3 px-6 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 shadow inline-block text-center">
                            Cancel Edit
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // Auto-hide success message after 2 seconds
    setTimeout(function() {
        const msg = document.getElementById('jobSuccessMsg');
        if (msg) {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                msg.remove();
            }, 500);
        }
    }, 2000);
</script>
<script src="assets/jquery.min.js"></script>
<script src="assets/validate.js"></script>
<?php include 'footer.php'; ?>