<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

// load categories once for nav dropdowns
$navCatResult = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
$navCategories = $navCatResult ? $navCatResult->fetch_all(MYSQLI_ASSOC) : [];
?>

<!-- Navbar -->
<nav class="bg-gray-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <!-- Brand -->
            <a href="#" class="text-2xl font-bold hover:text-indigo-400">Workaholic</a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-8 items-center">

                <a href="index.php" class="hover:text-indigo-400 px-3 py-2 text-sm font-medium">Home</a>

                <?php
                $role = $_SESSION['role'] ?? null;
                ?>

                <!-- Company dropdown (SHOW only if NOT company) -->
                <?php if ($role !== 'company'): ?>
                    <div class="relative group">
                        <button class="hover:text-indigo-400 px-3 py-2 text-sm font-medium flex items-center">
                            Company
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="absolute left-0 top-full w-44 bg-gray-700 rounded-md shadow-lg
                hidden group-hover:block group-hover:flex hover:flex flex-col z-20">
                            <a href="company.php"
                                class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                                All Companies
                            </a>
                            <div class="border-t border-gray-600 my-1"></div>
                            <?php foreach ($navCategories as $cat): ?>
                                <a href="company.php?category_id=<?php echo (int)$cat['category_id']; ?>"
                                    class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Freelancer link (SHOW only if NOT freelancer) -->
                <?php if ($role !== 'freelancer'): ?>
                    <a href="freelancer.php" class="hover:text-indigo-400 px-3 py-2 text-sm font-medium">Freelancer</a>
                <?php endif; ?>

                <a href="review.php" class="hover:text-indigo-400 px-3 py-2 text-sm font-medium">Reviews</a>

                <!-- Info Dropdown -->
                <div class="relative group">
                    <button class="hover:text-indigo-400 px-3 py-2 text-sm font-medium flex items-center">
                        Info
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div class="absolute right-0 top-full w-40 bg-gray-700 rounded-md shadow-lg 
                    hidden group-hover:block group-hover:flex hover:flex flex-col">

                        <a href="about.php"
                            class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                            About
                        </a>

                        <a href="service.php"
                            class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                            Services
                        </a>
                    </div>
                </div>

                <a href="contact.php" class="hover:text-indigo-400 px-3 py-2 text-sm font-medium">Contact</a>

                <!-- LOGIN / SIGNUP / PROFILE -->
                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>

                    <!-- Show Profile + Logout -->
                    <div class="relative group">
                        <button class="hover:text-indigo-400 px-3 py-2 text-sm font-medium flex items-center">
                            <?php
                            $displayName = $_SESSION['username'] ?? 'Profile';
                            echo htmlspecialchars($displayName);
                            ?>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="absolute right-0 top-full w-40 bg-gray-700 rounded-md shadow-lg
                        hidden group-hover:block hover:flex flex-col">

                            <a href="profile.php"
                                class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                                Profile
                            </a>

                            <a href="logout.php"
                                class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                                Logout
                            </a>
                        </div>
                    </div>

                <?php else: ?>

                    <!-- Show Sign Up dropdown -->
                    <div class="relative group">
                        <button class="hover:text-indigo-400 px-3 py-2 text-sm font-medium flex items-center">
                            Sign Up
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div class="absolute right-0 top-full w-40 bg-gray-700 rounded-md shadow-lg
                        hidden group-hover:block group-hover:flex hover:flex flex-col">

                            <a href="login.php"
                                class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                                Login
                            </a>

                            <a href="register.php"
                                class="block px-4 py-2 text-sm hover:bg-indigo-500 hover:text-white hover:rounded-md">
                                Register
                            </a>
                        </div>
                    </div>

                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <button
                id="menu-btn"
                class="md:hidden focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg
                    id="menu-open"
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg
                    id="menu-close"
                    class="h-6 w-6 hidden"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden bg-gray-700 hidden px-2 pt-2 pb-4 space-y-1">

        <a href="index.php" class="block px-3 py-2 rounded-md text-base hover:bg-indigo-500">Home</a>

        <?php if ($role !== 'company'): ?>
            <!-- Company toggle button -->
            <button
                class="w-full flex justify-between items-center px-3 py-2 text-base font-semibold text-gray-100 hover:bg-indigo-500"
                onclick="toggleDropdown('companyMenu')">
                Company
                <span class="text-xl font-bold">+</span>
            </button>

            <!-- Company submenu (hidden by default) -->
            <div id="companyMenu" class="hidden pl-5 space-y-1">
                <a href="company.php" class="block py-2 rounded-md hover:bg-indigo-500">All Companies</a>

                <?php foreach ($navCategories as $cat): ?>
                    <a href="company.php?category_id=<?php echo (int)$cat['category_id']; ?>"
                        class="block py-2 rounded-md hover:bg-indigo-500">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($role !== 'freelancer'): ?>
            <a href="freelancer.php" class="block px-3 py-2 rounded-md text-base hover:bg-indigo-500">Freelancer</a>
        <?php endif; ?>

        <a href="review.php" class="block px-3 py-2 rounded-md text-base hover:bg-indigo-500">Reviews</a>

        <!-- Info toggle button -->
        <button
            class="w-full flex justify-between items-center px-3 py-2 text-base font-semibold text-gray-100 hover:bg-indigo-500"
            onclick="toggleDropdown('infoMenu')">
            Info
            <span class="text-xl font-bold">+</span>
        </button>

        <!-- Info submenu -->
        <div id="infoMenu" class="hidden pl-5 space-y-1">
            <a href="about.php" class="block py-2 rounded-md hover:bg-indigo-500">About</a>
            <a href="service.php" class="block py-2 rounded-md hover:bg-indigo-500">Services</a>
        </div>

        <a href="contact.php" class="block px-3 py-2 rounded-md text-base hover:bg-indigo-500">Contact</a>

        <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
            <!-- Account toggle -->
            <button
                class="w-full flex justify-between items-center px-3 py-2 text-base font-semibold text-gray-100 hover:bg-indigo-500"
                onclick="toggleDropdown('accountMenu')">
                Account
                <span class="text-xl font-bold">+</span>
            </button>

            <div id="accountMenu" class="hidden pl-5 space-y-1">
                <a href="profile.php" class="block py-2 rounded-md hover:bg-indigo-500">Profile</a>
                <a href="logout.php" class="block py-2 rounded-md hover:bg-indigo-500">Logout</a>
            </div>

        <?php else: ?>

            <!-- Sign Up toggle -->
            <button
                class="w-full flex justify-between items-center px-3 py-2 text-base font-semibold text-gray-100 hover:bg-indigo-500"
                onclick="toggleDropdown('signupMenu')">
                Sign Up
                <span class="text-xl font-bold">+</span>
            </button>

            <div id="signupMenu" class="hidden pl-5 space-y-1">
                <a href="login.php" class="block py-2 rounded-md hover:bg-indigo-500">Login</a>
                <a href="register.php" class="block py-2 rounded-md hover:bg-indigo-500">Register</a>
            </div>

        <?php endif; ?>
    </div>
</nav>

<!-- Mobile dropdown toggle script -->
<script>
    function toggleDropdown(id) {
        document.getElementById(id).classList.toggle("hidden");
    }
</script>

<!-- Mobile menu toggle script -->
<script>
    const btn = document.getElementById("menu-btn");
    const menu = document.getElementById("mobile-menu");
    const menuOpenIcon = document.getElementById("menu-open");
    const menuCloseIcon = document.getElementById("menu-close");

    btn.addEventListener("click", () => {
        menu.classList.toggle("hidden");
        menuOpenIcon.classList.toggle("hidden");
        menuCloseIcon.classList.toggle("hidden");
    });
</script>