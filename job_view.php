<?php
$page_title = "Jobs";
include 'header.php';
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_role = $_SESSION['role'] ?? '';
?>

<section class="bg-gray-100 py-16">
    <div class="max-w-6xl mx-auto px-6">

        <h1 class="text-4xl font-extrabold text-center mb-12">
            Available <span class="text-blue-600">Jobs</span>
        </h1>

        <?php
        /* Fetch all jobs with company + category + proposal count */
        $sql = "
            SELECT 
                j.jobs_id,
                j.title,
                j.description,
                j.budget,
                j.status,
                j.created_at,
                c.company_id,
                c.company_name,
                cat.category_name,
                COUNT(p.proposal_id) AS proposal_count
            FROM jobs j
            JOIN companies c ON c.company_id = j.company_id
            JOIN categories cat ON cat.category_id = j.category_id
            LEFT JOIN proposals p ON p.job_id = j.jobs_id
            GROUP BY j.jobs_id
            ORDER BY j.created_at DESC
        ";

        $jobs = mysqli_query($conn, $sql);

        // Group jobs by company
        $groupedJobs = [];
        while ($job = mysqli_fetch_assoc($jobs)) {
            $groupedJobs[$job['company_id']]['company_name'] = $job['company_name'];
            $groupedJobs[$job['company_id']]['jobs'][] = $job;
        }
        ?>

        <?php if (!empty($groupedJobs)): ?>

            <div class="space-y-8">

                <?php foreach ($groupedJobs as $company_id => $companyData): ?>

                    <div class="bg-white shadow-lg rounded-xl p-6">

                        <!-- COMPANY HEADER -->
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold">
                                <?php echo htmlspecialchars($companyData['company_name']); ?>
                            </h2>

                            <a href="company_details.php?id=<?php echo (int)$company_id; ?>"
                                class="text-sm bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-md">
                                View Company
                            </a>
                        </div>

                        <!-- JOBS UNDER THIS COMPANY -->
                        <div class="space-y-5">

                            <?php foreach ($companyData['jobs'] as $job): ?>

                                <div class="border border-gray-200 rounded-lg p-4">

                                    <h3 class="text-xl font-semibold mb-1">
                                        <?php echo htmlspecialchars($job['title']); ?>
                                    </h3>

                                    <p class="text-sm text-gray-600 mb-1">
                                        Category: <strong><?php echo htmlspecialchars($job['category_name']); ?></strong>
                                    </p>

                                    <p class="text-sm text-gray-600 mb-1">
                                        Budget: <strong class="text-green-600">₹<?php echo htmlspecialchars($job['budget']); ?></strong>
                                    </p>

                                    <p class="text-sm text-gray-600 mb-1">
                                        Proposals: <strong><?php echo (int)$job['proposal_count']; ?></strong>
                                    </p>

                                    <p class="text-sm mb-2">
                                        Status:
                                        <span class="font-semibold
                                        <?php echo in_array($job['status'], ['completed', 'close']) ? 'text-red-600' : 'text-green-600'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $job['status'])); ?>
                                        </span>
                                    </p>

                                    <p class="text-gray-700 text-sm mb-3">
                                        <?php
                                        $desc = $job['description'] ?? '';
                                        echo htmlspecialchars(mb_substr($desc, 0, 120)) . (strlen($desc) > 120 ? '...' : '');
                                        ?>
                                    </p>

                                    <!-- ACTIONS -->
                                    <div class="flex flex-wrap gap-3">

                                        <?php if ($user_role === 'freelancer'): ?>

                                            <?php if (in_array($job['status'], ['completed', 'close'])): ?>
                                                <span class="text-sm text-red-600 font-semibold">
                                                    Applications Closed
                                                </span>
                                            <?php else: ?>
                                                <a href="proposal.php?job_id=<?php echo (int)$job['jobs_id']; ?>"
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                                                    Apply Now
                                                </a>
                                            <?php endif; ?>

                                        <?php elseif (!isset($_SESSION['user_id'])): ?>

                                            <a href="login.php" class="text-blue-600 text-sm font-semibold hover:underline">
                                                Login to apply →
                                            </a>

                                        <?php else: ?>

                                            <span class="text-xs text-gray-500">
                                                Only freelancers can apply
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <!-- DATA NOT FOUND -->
            <div class="text-center py-16">
                <h3 class="text-2xl font-bold text-gray-700">
                    Data not found
                </h3>
                <p class="text-gray-500 mt-2">
                    No jobs have been posted yet.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include 'footer.php'; ?>