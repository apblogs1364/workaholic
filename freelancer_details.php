<?php
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    echo "<section class='bg-gray-100 py-16'>
            <div class='max-w-3xl mx-auto px-6 text-center'>
                <h2 class='text-2xl font-bold text-red-600 mb-4'>Login required</h2>
                <p class='text-gray-700 mb-6'>Please login to view freelancer details.</p>
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
    echo "<h2 class='text-center text-red-600 mt-10'>Invalid freelancer ID.</h2>";
    include 'footer.php';
    exit;
}

$freelancer_id = intval($_GET['id']);

// Fetch freelancer base info
$f_stmt = $conn->prepare("
    SELECT f.*, u.username, u.user_email, u.user_phone, u.profile_img 
    FROM freelancers f
    JOIN users u ON f.user_id = u.user_id
    WHERE f.freelancer_id = ?
");
$f_stmt->bind_param("i", $freelancer_id);
$f_stmt->execute();
$f_res = $f_stmt->get_result();

if ($f_res->num_rows === 0) {
    echo "<h2 class='text-center text-red-600 mt-10'>Freelancer not found.</h2>";
    include 'footer.php';
    exit;
}

$freelancer = $f_res->fetch_assoc();
$profile_img = $freelancer['profile_img'] ?: "Images/default.png";
?>

<section class="bg-gray-100 py-16">
    <div class="max-w-5xl mx-auto px-6">

        <h1 class="text-4xl font-extrabold text-center mb-10">
            <?php
            $fullname = trim(($freelancer['fname'] ?? "") . " " . ($freelancer['lname'] ?? ""));
            echo htmlspecialchars($fullname !== "" ? $fullname : $freelancer['username']);
            ?>
        </h1>

        <div class="bg-white shadow-lg p-10 rounded-xl">

            <div class="flex justify-center mb-6">
                <img src="<?php echo htmlspecialchars($profile_img); ?>"
                    class="w-32 h-32 rounded-full object-cover border-4 border-indigo-500 shadow-lg">
            </div>

            <h2 class="text-2xl font-bold mb-4">About Freelancer</h2>

            <p class="text-gray-700 mb-4"><strong>Name:</strong>
                <?php echo htmlspecialchars($fullname !== "" ? $fullname : $freelancer['username']); ?>
            </p>

            <p class="text-gray-700 mb-4">
                <strong>Email:</strong> <?php echo htmlspecialchars($freelancer['user_email']); ?>
            </p>

            <p class="text-gray-700 mb-4">
                <strong>Phone:</strong> <?php echo htmlspecialchars($freelancer['user_phone']); ?>
            </p>

            <?php if (!empty($freelancer['bio'])): ?>
                <p class="text-gray-700 leading-relaxed mb-6">
                    <strong>Bio:</strong>
                    <?php echo nl2br(htmlspecialchars($freelancer['bio'])); ?>
                </p>
            <?php endif; ?>

            <p class="text-gray-700 mb-4">
                <strong>Skills:</strong>
                <?php echo !empty($freelancer['skills']) ? nl2br(htmlspecialchars($freelancer['skills'])) : "Not provided"; ?>
            </p>

            <p class="text-gray-700 mb-4">
                <strong>Experience:</strong>
                <?php echo !empty($freelancer['experience_year']) ? htmlspecialchars($freelancer['experience_year']) . " years" : "Not specified"; ?>
            </p>

            <?php if (!empty($freelancer['portfolio_url'])): ?>
                <p class="text-gray-700 mb-4">
                    <strong>Portfolio:</strong>
                    <a href="<?php echo htmlspecialchars($freelancer['portfolio_url']); ?>" target="_blank"
                        class="text-blue-600 underline ml-1">
                        View Portfolio
                    </a>
                </p>
            <?php endif; ?>

            <hr class="my-8">

            <h2 class="text-2xl font-bold mb-4">Work With This Freelancer</h2>

            <?php if ($user_role === 'company'): ?>
                <a href="contract.php?freelancer_id=<?php echo (int)$freelancer_id; ?>"
                    class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg text-lg">
                    Hire This Freelancer
                </a>
            <?php else: ?>
                <p class="text-gray-500 text-sm">
                    Only companies can hire freelancers.
                </p>
            <?php endif; ?>

        </div>

    </div>
</section>

<?php include 'footer.php'; ?>