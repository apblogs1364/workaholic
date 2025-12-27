<?php
$page_title = "Contract Details";
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['company', 'freelancer'])) {
    echo "<h2 class='text-center text-red-600 mt-10'>Access denied. Login required.</h2>";
    include 'footer.php';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

if (!isset($_GET['id'])) {
    echo "<h2 class='text-center text-red-600 mt-10'>Contract ID missing.</h2>";
    include 'footer.php';
    exit;
}

$contract_id = (int)$_GET['id'];

// --------------------- HANDLE MARK COMPLETE ---------------------
if ($role === 'company' && isset($_GET['action']) && $_GET['action'] === 'complete') {
    // Update status to completed and set end_date to today
    $stmt = $conn->prepare("UPDATE contracts SET status = 'completed', end_date = CURDATE() WHERE contract_id = ?");
    $stmt->bind_param("i", $contract_id);
    $stmt->execute();
    header("Location: contract_details.php?id=$contract_id");
    exit;
}

// --------------------- FETCH CONTRACT DETAILS ---------------------
$sql = "
    SELECT 
        con.*,
        j.title AS job_title,
        j.description AS job_description,
        
        f.freelancer_id,
        f.fname AS freelancer_fname,
        f.lname AS freelancer_lname,
        fu.username AS freelancer_username,
        fu.user_email AS freelancer_email,
        fu.user_phone AS freelancer_phone,
        
        c.company_id,
        c.company_name,
        cu.username AS company_username,
        cu.user_email AS company_email,
        cu.user_phone AS company_phone

    FROM contracts con
    LEFT JOIN jobs j ON j.jobs_id = con.job_id
    LEFT JOIN freelancers f ON f.freelancer_id = con.freelancer_id
    LEFT JOIN users fu ON fu.user_id = f.user_id
    LEFT JOIN companies c ON c.company_id = con.company_id
    LEFT JOIN users cu ON cu.user_id = c.user_id
    WHERE con.contract_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contract_id);
$stmt->execute();
$contract = $stmt->get_result()->fetch_assoc();

if (!$contract) {
    echo "<h2 class='text-center text-red-600 mt-10'>Contract not found.</h2>";
    include 'footer.php';
    exit;
}

// Determine if current user is allowed to view actions
$isCompany = ($role === 'company' && $contract['company_id'] == $user_id);
$isFreelancer = ($role === 'freelancer' && $contract['freelancer_id'] == $user_id);

?>

<section class="bg-gray-100 py-16">
    <div class="max-w-4xl mx-auto px-6 bg-white shadow-lg rounded-xl p-8">
        <h1 class="text-3xl font-bold mb-6 text-center">Contract Details</h1>

        <h2 class="text-xl font-semibold mb-2">Job Information</h2>
        <p><strong>Title:</strong> <?= htmlspecialchars($contract['job_title']) ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($contract['job_description']) ?></p>
        <p><strong>Agreed Amount:</strong> ₹<?= number_format($contract['agreed_amount']) ?></p>
        <p><strong>Status:</strong> <?= ucfirst($contract['status']) ?></p>
        <p><strong>Start Date:</strong>
            <?= !empty($contract['start_date']) ? date('Y-m-d', strtotime($contract['start_date'])) : '' ?>
        </p>
        <p><strong>End Date:</strong>
            <?= !empty($contract['end_date']) ? date('Y-m-d', strtotime($contract['end_date'])) : '' ?>
        </p>

        <hr class="my-4">

        <h2 class="text-xl font-semibold mb-2">Company Information</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($contract['company_name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($contract['company_email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($contract['company_phone']) ?></p>

        <hr class="my-4">

        <h2 class="text-xl font-semibold mb-2">Freelancer Information</h2>
        <p><strong>Name:</strong> <?= htmlspecialchars($contract['freelancer_fname'] . ' ' . $contract['freelancer_lname']) ?></p>
        <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($contract['freelancer_email']) ?>" class="text-blue-600"><?= htmlspecialchars($contract['freelancer_email']) ?></a></p>
        <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($contract['freelancer_phone']) ?>" class="text-blue-600"><?= htmlspecialchars($contract['freelancer_phone']) ?></a></p>

        <hr class="my-4">

        <div class="flex gap-4 mt-4">
            <?php if ($role === 'company'): ?>
                <?php if ($contract['status'] === 'active'): ?>
                    <a href="contract_details.php?id=<?= $contract_id ?>&action=complete" class="bg-green-600 text-white px-4 py-2 rounded-md">Mark Complete</a>
                    <a href="update_contract_status.php?id=<?= $contract_id ?>&action=cancel" class="bg-red-600 text-white px-4 py-2 rounded-md">Cancel</a>
                    <a href="payment.php?contract_id=<?= $contract_id ?>" class="bg-blue-600 text-white px-4 py-2 rounded-md">Payment</a>
                <?php elseif ($contract['status'] === 'completed'): ?>
                    <span class="text-green-600 font-semibold">Contract Completed</span>
                <?php elseif ($contract['status'] === 'cancelled'): ?>
                    <span class="text-red-600 font-semibold">Contract Cancelled</span>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Chat button for both -->
            <a href="chat.php?contract_id=<?= $contract_id ?>" class="bg-gray-700 text-white px-4 py-2 rounded-md">Chat</a>
        </div>
</section>

<?php include 'footer.php'; ?>