<?php
$page_title = "Company";
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$categoryFilter = "";

if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = (int) $_GET['category_id'];
    $categoryFilter = "WHERE c.category_id = $categoryId";
}

?>

<section class="bg-gray-100 py-16">
    <div class="max-w-6xl mx-auto px-6">

        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'freelancer') { ?>
            <h1 class="text-4xl font-extrabold text-center mb-12">
                Our <span class="text-blue-600">Company</span>
            </h1>

            <!-- Overview -->
            <div class="bg-white shadow-lg p-10 rounded-xl mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Company Overview</h2>
                <p class="text-gray-700 leading-relaxed">
                    Workaholic is a digital solutions provider delivering world-class web,
                    app, branding, and freelancing services across the globe. Trusted by
                    clients and businesses of all sizes.
                </p>
            </div>

            <!-- Workflow -->
            <h2 class="text-3xl font-bold text-center mb-6">How We Work</h2>
            <div class="grid md:grid-cols-4 gap-8">

                <div class="bg-white shadow p-6 rounded-xl text-center">
                    <h3 class="font-bold text-xl mb-2">1. Discussion</h3>
                    <p class="text-gray-600">Understanding your requirements.</p>
                </div>

                <div class="bg-white shadow p-6 rounded-xl text-center">
                    <h3 class="font-bold text-xl mb-2">2. Planning</h3>
                    <p class="text-gray-600">Project roadmap & strategy.</p>
                </div>

                <div class="bg-white shadow p-6 rounded-xl text-center">
                    <h3 class="font-bold text-xl mb-2">3. Execution</h3>
                    <p class="text-gray-600">Designing & developing the product.</p>
                </div>

                <div class="bg-white shadow p-6 rounded-xl text-center">
                    <h3 class="font-bold text-xl mb-2">4. Delivery</h3>
                    <p class="text-gray-600">Testing, deployment & support.</p>
                </div>

            </div>

            <!-- CTA moved to bottom -->
            <div class="text-center mt-16">
                <a href="register.php"
                    class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">
                    Join as Company
                </a>
            </div>
        <?php } ?>

        <!-- Companies List -->
        <?php
        // Companies with jobs first, then others (joined with users for profile_img)
        $sql = "
        SELECT c.*,
               COUNT(j.jobs_id) AS job_count,
               u.profile_img,
               cat.category_name
        FROM companies c
        LEFT JOIN jobs j ON j.company_id = c.company_id
        JOIN users u ON u.user_id = c.user_id
        JOIN categories cat ON cat.category_id = c.category_id
        $categoryFilter
        GROUP BY c.company_id
        ORDER BY job_count DESC, c.created_at DESC
    ";
        $companies = mysqli_query($conn, $sql);
        ?>

        <h2 class="text-3xl font-bold text-center mt-10 mb-8">Companies</h2>

        <div class="grid md:grid-cols-3 gap-10 text-center">
            <?php if (mysqli_num_rows($companies) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($companies)) { ?>
                    <div class="bg-white shadow-lg p-6 rounded-xl">

                        <?php
                        $img = !empty($row['profile_img']) ? $row['profile_img'] : 'Images/default.png';
                        ?>
                        <div class="mb-4 flex justify-center">
                            <img src="<?php echo htmlspecialchars($img); ?>"
                                class="w-16 h-16 rounded-full object-cover border">
                        </div>

                        <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($row['company_name']); ?></h3>

                        <p class="text-gray-600 mb-1 text-sm">
                            Jobs posted: <?php echo (int)$row['job_count']; ?>
                        </p>

                        <div class="mt-4">
                            <?php if (isset($_SESSION['user_id'])) { ?>
                                <!-- Logged-in user: go to company details -->
                                <a href="company_details.php?id=<?php echo (int)$row['company_id']; ?>"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg inline-block">
                                    View Details →
                                </a>
                            <?php } else { ?>
                                <!-- Guest must login first -->
                                <a href="login.php"
                                    class="text-blue-600 font-semibold hover:underline">
                                    Login to view company details →
                                </a>
                            <?php } ?>
                        </div>

                    </div>
                <?php } ?>

            <?php } else { ?>
                <!-- DATA NOT FOUND MESSAGE -->
                <div class="col-span-full text-center py-16">
                    <h3 class="text-2xl font-bold text-gray-700">
                        Data not found
                    </h3>
                    <p class="text-gray-500 mt-2">
                        No companies found under this category.
                    </p>
                </div>
            <?php } ?>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>
</body>