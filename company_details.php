<?php
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login before viewing any company detail
if (!isset($_SESSION['user_id'])) {
    echo "<section class='bg-gray-100 py-16'>
            <div class='max-w-3xl mx-auto px-6 text-center'>
                <h2 class='text-2xl font-bold text-red-600 mb-4'>Login required</h2>
                <p class='text-gray-700 mb-6'>Please login to view company details.</p>
                <a href='login.php' class='bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-lg'>
                    Go to Login
                </a>
            </div>
          </section>";
    include 'footer.php';
    exit;
}

$user_role = $_SESSION['role'] ?? '';

// Validate ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<h2 class='text-center text-red-600 mt-10'>Invalid company ID.</h2>";
    include 'footer.php';
    exit;
}

$company_id = intval($_GET['id']);

// Fetch company details dynamically
$company_stmt = $conn->prepare("SELECT * FROM companies WHERE company_id = ?");
$company_stmt->bind_param("i", $company_id);
$company_stmt->execute();
$company_res = $company_stmt->get_result();

if ($company_res->num_rows === 0) {
    echo "<h2 class='text-center text-red-600 mt-10'>Company not found.</h2>";
    include 'footer.php';
    exit;
}

$company = $company_res->fetch_assoc();

// Fetch profile image from users table using company.user_id
$uid = (int)$company['user_id'];

$img_stmt = $conn->prepare("SELECT profile_img FROM users WHERE user_id = ?");
$img_stmt->bind_param("i", $uid);
$img_stmt->execute();
$img_res = $img_stmt->get_result()->fetch_assoc();

$company_profile_img = $img_res['profile_img'] ?: "Images/default.png";

// Fetch ONLY this company's jobs
$jobs_sql = "SELECT jobs_id, title, description, budget, status, created_at 
             FROM jobs 
             WHERE company_id = ?
             ORDER BY created_at DESC";
$stmtJobs = $conn->prepare($jobs_sql);
$stmtJobs->bind_param("i", $company_id);
$stmtJobs->execute();
$jobs_result = $stmtJobs->get_result();
?>

<section class="bg-gray-100 py-16">
    <div class="max-w-5xl mx-auto px-6">

        <h1 class="text-4xl font-extrabold text-center mb-10">
            <?php echo htmlspecialchars($company['company_name']); ?>
        </h1>

        <div class="bg-white shadow-lg p-10 rounded-xl">

            <div class="flex justify-center mb-6">
                <img src="<?php echo htmlspecialchars($company_profile_img); ?>"
                    class="w-32 h-32 rounded-full object-cover border-4 border-indigo-500 shadow-lg">
            </div>

            <h2 class="text-2xl font-bold mb-4">About Company</h2>
            <p class="text-gray-700 leading-relaxed mb-6">
                <?php echo nl2br(htmlspecialchars($company['company_description'])); ?>
            </p>

            <p class="text-gray-700 mb-2">
                <strong>Name: </strong><?php echo htmlspecialchars($company['company_name']); ?>
            </p>
            <p class="text-gray-700 mb-2">
                <strong>Address: </strong><?php echo htmlspecialchars($company['company_address']); ?>
            </p>
            <?php if (!empty($company['company_website'])) { ?>
                <p class="text-gray-700 mb-2">
                    <strong>Website: </strong>
                    <a href="<?php echo htmlspecialchars($company['company_website']); ?>" target="_blank" class="text-blue-600 underline">
                        <?php echo htmlspecialchars($company['company_website']); ?>
                    </a>
                </p>
            <?php } ?>

            <?php
            $type_name = "Not added";
            if (!empty($company['category_id'])) {
                $cat_id = (int)$company['category_id'];
                $cat_stmt = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
                $cat_stmt->bind_param("i", $cat_id);
                $cat_stmt->execute();
                $cat_res = $cat_stmt->get_result()->fetch_assoc();
                if ($cat_res) {
                    $type_name = $cat_res['category_name'];
                }
            }
            ?>
            <p class="text-gray-700 mb-6">
                <strong>Business Type: </strong><?php echo htmlspecialchars($type_name); ?>
            </p>


            <!-- JOIN BUTTON ONLY IF LOGGED IN (already enforced at top) 
             <hr class="my-6">
            <a href="join_company.php?id=<?php echo (int)$company_id; ?>"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-lg">
                Join This Company
            </a> -->

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4">Job Openings</h2>

            <?php if ($jobs_result->num_rows === 0): ?>
                <p class="text-gray-600">This company has not posted any jobs yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php while ($job = $jobs_result->fetch_assoc()): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-xl font-semibold mb-1">
                                <?php echo htmlspecialchars($job['title']); ?>
                            </h3>
                            <p class="text-gray-700 text-sm mb-2">
                                <?php
                                $jdesc = $job['description'] ?? '';
                                echo htmlspecialchars(mb_substr($jdesc, 0, 150)) . (strlen($jdesc) > 150 ? "..." : "");
                                ?>
                            </p>
                            <p class="text-gray-600 text-sm mb-1">
                                <strong>Budget:</strong> <?php echo htmlspecialchars($job['budget']); ?>
                            </p>
                            <p class="text-gray-600 text-sm mb-3">
                                <strong>Status:</strong> <?php echo htmlspecialchars($job['status']); ?>
                                · <strong>Posted:</strong> <?php echo htmlspecialchars($job['created_at']); ?>
                            </p>

                            <?php if ($user_role === 'freelancer'): ?>
                                <?php if (in_array($job['status'], ['completed', 'close'])): ?>
                                    <span class="text-sm text-red-600 font-semibold">
                                        Applications Closed
                                    </span>
                                <?php else: ?>
                                    <a href="proposal.php?job_id=<?php echo (int)$job['jobs_id']; ?>"
                                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                                        Apply Now
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-500">Only freelancers can apply to jobs.</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

        </div>

    </div>
</section>

<?php include 'footer.php'; ?>