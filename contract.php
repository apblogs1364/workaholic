<?php
$page_title = "Contracts";
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allow BOTH company and freelancer
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['company', 'freelancer'])) {
    echo "<h2 class='text-center text-red-600 mt-10'>Access denied. Login required.</h2>";
    include 'footer.php';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

$company_id = 0;
$freelancer_id = 0;

/* ---------- GET COMPANY OR FREELANCER ID ---------- */

// Get company_id (if exists)
$stmt = $conn->prepare("SELECT company_id FROM companies WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$company_id = $company['company_id'] ?? 0;

// Get freelancer_id (if exists)
$stmt = $conn->prepare("SELECT freelancer_id FROM freelancers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$freelancer = $stmt->get_result()->fetch_assoc();
$freelancer_id = $freelancer['freelancer_id'] ?? 0;

// If user has neither profile
if ($company_id === 0 && $freelancer_id === 0) {
    echo "<h2 class='text-center text-red-600 mt-10'>Profile not found.</h2>";
    include 'footer.php';
    exit;
}

// Allow BOTH sides to see contracts
if ($role === 'company') {
    $whereCondition = "con.company_id = ?";
    $bindTypes = "i";
    $bindValues = [$company_id];
} else {
    $whereCondition = "con.freelancer_id = ?";
    $bindTypes = "i";
    $bindValues = [$freelancer_id];
}

/* ---------- FETCH CONTRACTS ---------- */
$sql = "
    SELECT 
        con.contract_id,
        con.job_id,
        con.agreed_amount,
        con.status AS contract_status,
        con.start_date,
        con.end_date,
        j.title AS job_title,
        f.freelancer_id,
        f.fname,
        f.lname
    FROM contracts con
    LEFT JOIN jobs j ON j.jobs_id = con.job_id
    LEFT JOIN freelancers f ON f.freelancer_id = con.freelancer_id
    WHERE $whereCondition
    ORDER BY con.start_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($bindTypes, ...$bindValues);
$stmt->execute();
$contracts = $stmt->get_result();
?>

<section class="bg-gray-100 py-16">
    <div class="max-w-6xl mx-auto px-6">
        <h1 class="text-4xl font-extrabold text-center mb-12">
            Your <span class="text-blue-600">Contracts</span>
        </h1>

        <?php if ($contracts->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($con = $contracts->fetch_assoc()): ?>
                    <div class="bg-white shadow-lg rounded-xl p-6 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold mb-1">
                                <?= htmlspecialchars($con['job_title'] ?? 'Job not available'); ?>
                            </h3>

                            <p class="text-gray-700 text-sm mb-1">
                                Freelancer:
                                <?= htmlspecialchars(
                                    ($con['fname'] && $con['lname'])
                                        ? $con['fname'] . ' ' . $con['lname']
                                        : 'Freelancer not available'
                                ); ?>
                            </p>

                            <p class="text-gray-700 text-sm mb-1">
                                Agreed Amount:
                                <span class="font-semibold text-green-600">
                                    ₹<?= htmlspecialchars($con['agreed_amount']); ?>
                                </span>
                            </p>

                            <p class="text-gray-700 text-sm mb-1">
                                Status:
                                <span class="<?php
                                                echo $con['contract_status'] === 'active'
                                                    ? 'text-blue-600'
                                                    : ($con['contract_status'] === 'completed'
                                                        ? 'text-green-600'
                                                        : 'text-red-600');
                                                ?> font-semibold">
                                    <?= ucfirst($con['contract_status']); ?>
                                </span>
                            </p>

                            <p class="text-gray-700 text-sm">
                                Start Date:
                                <?= !empty($con['start_date']) ? date('Y-m-d', strtotime($con['start_date'])) : 'N/A'; ?>
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <a href="contract_details.php?id=<?= (int)$con['contract_id']; ?>"
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-md text-sm">
                                More Details
                            </a>

                            <a href="chat.php?contract_id=<?= (int)$con['contract_id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                                Chat
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16">
                <h3 class="text-2xl font-bold text-gray-700">No contracts found</h3>
                <p class="text-gray-500 mt-2">You have not made any contracts yet.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>