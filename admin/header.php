<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - Workaholic</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="flex">

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="w-64 h-screen bg-gradient-to-b from-indigo-700 to-indigo-900 text-white fixed top-0 left-0 shadow-lg">

            <div class="px-6 py-6">
                <h1 class="text-xl font-bold tracking-wide">Admin Panel</h1>
            </div>

            <nav class="mt-4 px-4 space-y-2">
                <a href="admin_dashboard.php"
                    class="block bg-indigo-600 px-4 py-3 rounded-md font-medium">Dashboard</a>

                <a href="manage_users.php" class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Manage Users
                </a>

                <a href="manage_clients.php" class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Manage Clients
                </a>

                <a href="manage_freelancers.php" class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Manage Freelancers
                </a>

                <a href="manage_contracts.php" class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Manage Contracts
                </a>

                <a href="payments.php" class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Payments
                </a>

                <a href="admin_logout.php"
                    class="block bg-red-600 px-4 py-3 rounded-md mt-6 hover:bg-red-500">
                    Logout
                </a>
            </nav>
        </aside>

        <!-- ===================== MAIN CONTENT WRAPPER ===================== -->
        <div class="flex-1 ml-64">

            <!-- ========= TOP NAVBAR (beside sidebar, not above) ========= -->
            <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
                <h2 class="text-xl font-semibold">Workaholic Admin Panel</h2>

                <span class="font-medium text-gray-700">
                    <?php echo $_SESSION['admin_name'] ?? "Admin"; ?>
                </span>
            </header>

            <!-- MAIN PAGE CONTENT START -->
            <main class="p-6">