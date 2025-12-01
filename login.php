<?php
session_start();
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 1. Check in admins table
    $stmt = $conn->prepare("SELECT * FROM admins WHERE admin_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['admin_password'])) {
            // Admin login successful
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['admin_name'];
            $_SESSION['role'] = 'admin';

            header("Location: admin/admin_dashboard.php");
            exit;
        } else {
            $error = "Incorrect password!";
        }
    } else {
        // 2. Check in users table
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['user_password'])) {
                // User login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // freelancer or company

                header("Location: index.php");
                exit;
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "No account found with this email!";
        }
    }
}

$page_title = "Login";
include 'header.php';
?>
<style>
    .error {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        line-height: 1.25;
    }

    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        width: 100%;
        transition: all 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
    }

    .form-label {
        display: block;
        margin-bottom: 0.25rem;
        color: #374151;
        font-weight: 500;
    }

    .form-check-input {
        border: 1px solid #d1d5db;
        border-radius: 0.25rem;
        accent-color: #4f46e5;
    }
</style>
<script src="assets/jquery.min.js"></script>
<script src="assets/validate.js"></script>


<section class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="container px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg flex flex-col md:flex-row overflow-hidden max-w-4xl mx-auto">

            <!-- Left Side -->
            <div class="bg-indigo-600 text-white flex flex-col justify-center items-center p-8 md:w-1/2 rounded-t-lg md:rounded-l-lg md:rounded-r-[4rem]">
                <img src="Images/sm2-removebg-preview.png" alt="Logo" class="mb-6 rounded-md shadow-md">
                <h2 class="text-2xl font-bold mb-2 text-center">Welcome to Workaholic</h2>
                <p class="text-sm text-center text-gray-200">
                    Welcome back, Workaholic — Let's get you back to work!
                </p>
            </div>

            <!-- Right Side: Login Form -->
            <div class="p-8 md:w-1/2 w-full">
                <h2 class="text-2xl font-bold text-center text-[#0F172A] mb-2">Log In to Your Account</h2>
                <p class="text-sm text-gray-600 text-center mb-6">
                    Unlock your potential <strong> — welcome back.</strong>
                </p>

                <?php if ($error != ""): ?>
                    <div class="text-red-500 text-center mb-4"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="" id="loginForm" method="POST" class="space-y-4">

                    <!-- Email -->
                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter email"
                            class="form-control" data-validation="required email">
                        <div class="error" id="emailError"></div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="form-label" for="pwd">Password</label>
                        <input type="password" id="pwd" name="password" placeholder="Enter password"
                            class="form-control" data-validation="required min max" data-min="8" data-max="25">
                        <div class="error" id="passwordError"></div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input mr-2">
                        <label for="remember" class="text-sm text-gray-700">Remember me</label>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg text-lg font-bold hover:bg-indigo-700 transition-all">
                        Log In
                    </button>
                </form>


                <p class="text-sm text-gray-600 mt-6 text-center">
                    Don’t have an account?
                    <a href="register.php" class="text-[#1E3A8A] hover:underline">Register here</a>
                    <br><br>
                    <a href="recover-password.php" class="text-[#1E3A8A] hover:underline">Forgot your password?</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>