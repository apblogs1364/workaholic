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
    <title><?php echo $page_title ?? "Admin Panel - Workaholic"; ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-100">

    <div class="flex">

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="w-64 h-screen bg-gradient-to-b from-indigo-700 to-indigo-900 text-white fixed top-0 left-0 shadow-lg">

            <div class="px-6 py-6">
                <h1 class="text-xl font-bold tracking-wide">Welcome, <?php echo $_SESSION['username'] ?? "Admin"; ?></h1>
            </div>

            <nav class="mt-4 px-4 space-y-2">
                <a href="admin_dashboard.php"
                    class="block hover:bg-indigo-600 px-4 py-3 rounded-md font-medium">Dashboard</a>

                <a href="manage_admins.php"
                    class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Manage Admins
                </a>

                <a href="manage_companies.php" class="block px-4 py-3 hover:bg-indigo-600 rounded-md">
                    Manage Companies
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

                <a href="../index.php"
                    class="block bg-red-600 px-4 py-3 rounded-md mt-6 hover:bg-red-500 flex items-center justify-center">
                    Visit User Side
                </a>
            </nav>
        </aside>

        <!-- ===================== MAIN CONTENT WRAPPER ===================== -->
        <div class="flex-1 ml-64">

            <!-- ========= TOP NAVBAR ========= -->
            <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900"><?php echo $page_title ?? "Admin Panel - Workaholic"; ?></h1>

                <a href="../logout.php"
                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500 flex items-center justify-center">
                    Logout
                </a>

            </header>

            <!-- MAIN PAGE CONTENT START -->
            <main class="p-6">