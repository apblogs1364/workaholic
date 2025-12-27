<?php
$page_title = "Freelancer";
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch freelancers joined with users so you have email/phone/profile image
$sql = "
    SELECT f.freelancer_id, f.fname, f.lname, f.bio, f.skills, f.experience_year, f.portfolio_url,
           u.user_id, u.username, u.user_email, u.user_phone, u.profile_img
    FROM freelancers f
    JOIN users u ON f.user_id = u.user_id
    ORDER BY f.created_at DESC
";
$result = mysqli_query($conn, $sql);
?>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-6">

        <h1 class="text-4xl font-extrabold text-center mb-10">
            Become a <span class="text-blue-600">Freelancer</span> with Us
        </h1>

        <p class="text-center max-w-3xl mx-auto text-gray-700 mb-12">
            Join our growing team of skilled freelancers. Work from anywhere, get
            real projects, and earn fairly with timely payouts.
        </p>

        <!-- Benefits -->
        <div class="grid md:grid-cols-3 gap-8">

            <div class="bg-gray-50 shadow p-6 rounded-xl text-center">
                <h2 class="text-xl font-bold mb-2">Flexible Work</h2>
                <p class="text-gray-600">Choose your own work hours and projects.</p>
            </div>

            <div class="bg-gray-50 shadow p-6 rounded-xl text-center">
                <h2 class="text-xl font-bold mb-2">Guaranteed Payments</h2>
                <p class="text-gray-600">On-time secure payments for every project.</p>
            </div>

            <div class="bg-gray-50 shadow p-6 rounded-xl text-center">
                <h2 class="text-xl font-bold mb-2">Real Projects</h2>
                <p class="text-gray-600">Web, app, design and marketing tasks.</p>
            </div>

        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <a href="register.php"
                class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">
                Join as Freelancer
            </a>
        </div>

        <!-- Available Freelancers -->
        <h2 class="text-3xl font-bold text-center mt-10 mb-8">Freelancers Team</h2>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <p class="text-center text-gray-600">No freelancers have registered yet.</p>
        <?php else: ?>
            <div class="grid md:grid-cols-3 gap-10">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="bg-white shadow-lg p-6 rounded-xl text-center">

                        <?php
                        $img = !empty($row['profile_img']) ? $row['profile_img'] : 'Images/default.png';
                        ?>
                        <div class="mb-4 flex justify-center">
                            <img src="<?php echo htmlspecialchars($img); ?>"
                                class="w-16 h-16 rounded-full object-cover border">
                        </div>

                        <!-- Name -->
                        <h3 class="text-2xl font-bold mb-2">
                            <?php
                            $name = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                            if ($name === "") {
                                $name = $row['username'] ?? "Freelancer";
                            }
                            echo htmlspecialchars($name);
                            ?>
                        </h3>

                        <!-- Small Bio Snippet (optional like company description) -->
                        <p class="text-gray-600 text-sm mb-4">
                            <?php
                            $bio = $row['bio'] ?? '';
                            echo $bio ? htmlspecialchars(mb_substr($bio, 0, 90)) . "..." : "Freelancer on our platform.";
                            ?>
                        </p>

                        <div class="mt-4">
                            <?php if (isset($_SESSION['user_id'])) { ?>
                                <!-- Logged-in users can view freelancer details -->
                                <a href="freelancer_details.php?id=<?php echo (int)$row['freelancer_id']; ?>"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg inline-block">
                                    View Details →
                                </a>
                            <?php } else { ?>
                                <!-- Guests must login -->
                                <a href="login.php"
                                    class="text-blue-600 font-semibold hover:underline">
                                    Login to view freelancer details →
                                </a>
                            <?php } ?>
                        </div>

                    </div>
                <?php } ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include 'footer.php'; ?>
</body>