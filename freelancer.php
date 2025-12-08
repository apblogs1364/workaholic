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
        <h2 class="text-3xl font-bold text-center mt-16 mb-8">Available Freelancers</h2>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <p class="text-center text-gray-600">No freelancers have registered yet.</p>
        <?php else: ?>
            <div class="grid md:grid-cols-3 gap-10">
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <div class="bg-gray-50 shadow p-6 rounded-xl">

                        <?php
                        $img = !empty($row['profile_img']) ? $row['profile_img'] : 'Images/default.png';
                        ?>
                        <div class="mb-4 flex justify-center">
                            <img src="<?php echo htmlspecialchars($img); ?>"
                                 class="w-16 h-16 rounded-full object-cover border">
                        </div>

                        <h3 class="text-xl font-bold mb-1">
                            <?php
                            $name = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                            if ($name === '') {
                                $name = $row['username'] ?? 'Freelancer';
                            }
                            echo htmlspecialchars($name);
                            ?>
                        </h3>

                        <p class="text-gray-600 text-sm mb-2">
                            <?php
                            $skills = $row['skills'] ?? '';
                            if ($skills !== '') {
                                echo "Skills: " . htmlspecialchars(mb_substr($skills, 0, 60)) . (strlen($skills) > 60 ? "..." : "");
                            } else {
                                echo "Skills not added yet.";
                            }
                            ?>
                        </p>

                        <p class="text-gray-600 text-sm mb-2">
                            <strong>Experience:</strong>
                            <?php
                            $exp = $row['experience_year'] ?? '';
                            echo $exp !== '' ? htmlspecialchars($exp) . " years" : "Not specified";
                            ?>
                        </p>

                        <?php if (!empty($row['portfolio_url'])) { ?>
                            <p class="text-gray-600 text-sm mb-4">
                                <a href="<?php echo htmlspecialchars($row['portfolio_url']); ?>" target="_blank"
                                   class="text-blue-600 underline">
                                    View Portfolio
                                </a>
                            </p>
                        <?php } else { ?>
                            <p class="text-gray-600 text-sm mb-4">Portfolio not added.</p>
                        <?php } ?>

                        <p class="text-gray-600 text-xs">
                            <strong>Email:</strong> <?php echo htmlspecialchars($row['user_email']); ?><br>
                            <strong>Phone:</strong> <?php echo htmlspecialchars($row['user_phone']); ?>
                        </p>

                    </div>
                <?php } ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include 'footer.php'; ?>
</body>
