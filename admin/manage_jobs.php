<?php
session_start();
include '../config.php';

$page_title = "Manage Jobs";
include 'header.php';

// ---------------- AUTH CHECK ----------------
// Only admin should access this page
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$job_success = "";
$job_error = "";

// Job status labels
$status_labels = [
    'open' => 'Open',
    'in_progress' => 'In Progress',
    'completed' => 'Completed',
    'close' => 'Close'
];


// ---------------- DELETE JOB ----------------
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $job_id = (int)$_GET['id'];

    // Delete proposals first
    $conn->query("DELETE FROM proposals WHERE job_id = $job_id");

    // Delete job
    if ($conn->query("DELETE FROM jobs WHERE jobs_id = $job_id")) {
        $job_success = "Job deleted successfully.";
    } else {
        $job_error = "Failed to delete job.";
    }
}


// ---------------- LOAD EDIT FORM ----------------
$edit_record = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $job_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM jobs WHERE jobs_id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $edit_record = $stmt->get_result()->fetch_assoc();
}


// ---------------- UPDATE JOB ----------------
if (isset($_POST['update_job'])) {

    $job_id = (int)$_POST['job_id'];
    $title = trim($_POST['job_title']);
    $description = trim($_POST['job_description']);
    $budget = trim($_POST['job_budget']);
    $status = trim($_POST['job_status']);

    if ($title === '' || $description === '' || $budget === '') {
        $job_error = "Please fill in all fields.";
    } else {

        $upd = $conn->prepare("
            UPDATE jobs 
            SET title = ?, description = ?, budget = ?, status = ?
            WHERE jobs_id = ?
        ");
        $upd->bind_param("ssssi", $title, $description, $budget, $status, $job_id);

        if ($upd->execute()) {
            header("Location: manage_jobs.php?success=1");
            exit();
        } else {
            $job_error = "Failed to update job.";
        }
    }
}

if (isset($_GET['success'])) {
    $job_success = "Job updated successfully.";
}
?>

<!-- ========================================= -->
<!-- MAIN PAGE UI -->
<!-- ========================================= -->

<main class="p-6">

    <h2 class="text-2xl font-semibold mb-6">Manage Jobs</h2>

    <!-- SUCCESS & ERROR -->
    <?php if ($job_success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $job_success ?></div>
    <?php endif; ?>

    <?php if ($job_error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $job_error ?></div>
    <?php endif; ?>


    <?php if (isset($_GET['action']) && $_GET['action'] === 'edit' && $edit_record): ?>

        <!-- ====================== EDIT JOB FORM ======================== -->
        <form method="POST" class="bg-white shadow rounded-xl p-6 max-w-3xl space-y-6 validateForm">

            <input type="hidden" name="job_id" value="<?= $edit_record['jobs_id'] ?>">

            <!-- TITLE -->
            <div>
                <label class="block font-medium mb-1">Job Title</label>
                <input type="text" name="job_title" value="<?= $edit_record['title'] ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="job_titleError"></p>
            </div>

            <!-- DESCRIPTION -->
            <div>
                <label class="block font-medium mb-1">Description</label>
                <textarea name="job_description"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required"><?= $edit_record['description'] ?></textarea>
                <p class="error text-red-500 text-sm mt-1" id="job_descriptionError"></p>
            </div>

            <!-- BUDGET -->
            <div>
                <label class="block font-medium mb-1">Budget</label>
                <input type="text" name="job_budget" value="<?= $edit_record['budget'] ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required numeric">
                <p class="error text-red-500 text-sm mt-1" id="job_budgetError"></p>
            </div>

            <!-- STATUS -->
            <div>
                <label class="block font-medium mb-1">Status</label>
                <select name="job_status" class="w-full border px-3 py-2 rounded" data-validation="required">
                    <?php foreach ($status_labels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $edit_record['status'] === $k ? 'selected' : '' ?>>
                            <?= $v ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="error text-red-500 text-sm mt-1" id="job_statusError"></p>
            </div>

            <!-- BUTTONS -->
            <div class="flex gap-4 mt-4">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded">
                    Update Job
                </button>
                <a href="manage_jobs.php" class="text-indigo-600 hover:underline">Back</a>
            </div>

            <input type="hidden" name="update_job" value="1">
        </form>

    <?php else: ?>

        <!-- ====================== LIST TABLE ======================== -->
        <?php
        $jobs = $conn->query("
            SELECT * FROM jobs 
            ORDER BY jobs_id DESC
        ");
        ?>

        <table class="min-w-full bg-white shadow rounded-xl overflow-hidden text-center">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Title</th>
                    <th class="p-3">Budget</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Created</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $jobs->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3"><?= $row['title'] ?></td>
                        <td class="p-3"><?= $row['budget'] ?></td>
                        <td class="p-3"><?= $status_labels[$row['status']] ?? $row['status'] ?></td>
                        <td class="p-3 text-gray-600"><?= date("d M Y", strtotime($row['created_at'])) ?></td>

                        <td class="p-3 flex gap-4">
                            <a href="manage_jobs.php?action=edit&id=<?= $row['jobs_id'] ?>"
                                class="text-indigo-600 hover:underline">Edit</a>

                            <a href="manage_jobs.php?action=delete&id=<?= $row['jobs_id'] ?>"
                                onclick="return confirm('Delete this job?')"
                                class="text-red-600 hover:underline">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php endif; ?>

    <script src="assets/jquery.min.js"></script>
    <script src="assets/validate.js?v=<?= time(); ?>"></script>
</main>
<?php include 'footer.php'; ?>