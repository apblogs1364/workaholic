<?php
session_start();
include '../config.php';
include 'header.php';

// ---- ACCESS CHECK ----
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['username'];
?>

<!-- Main Wrapper -->
<div class="min-h-screen flex bg-gray-100">


    <!-- Content Area -->
    <main class="flex-1 p-8">

        <!-- Statistic Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Admins</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">20</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Companies</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">120</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Freelancers</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">340</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Categories</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">5</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Jobs</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">95</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Proposals</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">90</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Contracts</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">85</p>
            </div>

            <div class="bg-white shadow rounded-xl p-6">
                <h3 class="text-gray-500">Total Earnings</h3>
                <p class="text-3xl font-bold text-indigo-700 mt-2">₹12,540</p>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="mt-12 bg-white shadow rounded-xl p-6">
            <h2 class="text-2xl font-semibold mb-4">Recent User Registrations</h2>

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 uppercase text-sm">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Joined</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    <?php
                    // Fetch last 10 users
                    $query = "SELECT user_id, username, user_email, role, created_at FROM users ORDER BY user_id DESC LIMIT 10";
                    $result = $conn->query($query);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "
                    <tr>
                        <td class='px-4 py-3 font-medium'>" . htmlspecialchars($row['username']) . "</td>
                        <td class='px-4 py-3'>" . htmlspecialchars($row['user_email']) . "</td>
                        <td class='px-4 py-3'>" . htmlspecialchars(ucfirst($row['role'])) . "</td>
                        <td class='px-4 py-3'>" . htmlspecialchars($row['created_at']) . "</td>

                        
                    </tr>";
                        }
                    } else {
                        echo "
                <tr>
                    <td colspan='4' class='text-center py-4 text-gray-500'>
                        No users found.
                    </td>
                </tr>";
                    }
                    ?>

                </tbody>
            </table>
        </div>

    </main>
</div>

<?php include 'footer.php'; ?>
</body>