<?php
include 'config.php';
session_start();

$page_title = "Register";
include 'header.php';



function clean($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = clean($_POST['username']);
    $email = clean($_POST['user_email']);
    $role = clean($_POST['role']);
    $phone = clean($_POST['user_phone']);
    $password = $_POST['user_password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match'); window.history.back();</script>";
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Upload profile picture
    $profilePath = "";
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $uploadDir = "uploads/profile/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES['profile_img']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetFile)) {
            $profilePath = $targetFile;
        }
    }

    // Check duplicate email
    $check = $conn->prepare("SELECT * FROM users WHERE user_email=?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='login.php';</script>";
        exit;
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, user_email, role, user_phone, user_password, profile_img, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $lname = ""; // keeping empty since no last name anymore
    $stmt->bind_param("ssssss", $username, $email, $role, $phone, $hashedPassword, $profilePath);
    $stmt->execute();

    $user_id = $stmt->insert_id;

    // Insert into role tables
    if ($role === "freelancer") {
        $conn->query("INSERT INTO freelancers (user_id, fname, lname, bio, skills, experience_year, portfolio_url, created_at) VALUES ($user_id,'','','','','','',NOW())");
    } elseif ($role === "company") {
        $conn->query("INSERT INTO companies (user_id, company_name, company_address, company_description, company_website, business_type, created_at) VALUES ($user_id,'','','','','',NOW())");
    }

    echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
}

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
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<script src="assets/validate.js"></script>

<div class="flex flex-col md:flex-row max-w-5xl mx-auto my-12 bg-white rounded-xl shadow-xl overflow-hidden">

    <!-- LEFT SIDE -->
    <div class="md:w-1/2 bg-gradient-to-br from-indigo-600 to-indigo-800 text-white flex flex-col items-center justify-center p-10">
        <img src="Images/sm3-removebg-preview.png" class="w-40 mb-6 drop-shadow-lg">

        <h2 class="text-3xl font-bold mb-2">Join Workaholic</h2>
        <p class="text-center text-gray-200 w-72">
            Create your account and start your journey—whether you're a client or freelancer.
        </p>

        <a href="login.php" class="mt-8 border border-white px-8 py-2 rounded-lg hover:bg-white hover:text-indigo-700 font-semibold transition-all">
            Already have an account? Login
        </a>
    </div>

    <!-- RIGHT SIDE -->
    <div class="md:w-1/2 p-10">
        <h1 class="text-3xl font-bold text-gray-800">Create Account</h1>
        <p class="text-gray-500 text-sm mb-6">Please fill in the details below.</p>

        <form action="" method="POST" enctype="multipart/form-data" id="regForm" class="space-y-4">

            <div>
                <label class="text-sm font-medium">Username</label>
                <input type="text" name="username"
                    class="form-control"
                    data-validation="required alpha">
                <div class="error" id="usernameError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="text" name="user_email"
                    class="form-control"
                    data-validation="required email">
                <div class="error" id="user_emailError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Select Role</label>
                <select name="role"
                    class="form-control"
                    data-validation="required">
                    <option value="">-- Select Role --</option>
                    <option value="company">Company</option>
                    <option value="freelancer">Freelancer</option>
                </select>
                <div class="error" id="roleError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Mobile Number</label>
                <input type="text" name="user_phone"
                    class="form-control"
                    data-validation="required numeric min max" data-min="10" data-max="10">
                <div class="error" id="user_phoneError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Password</label>
                <input type="password" name="user_password" id="password"
                    class="form-control"
                    data-validation="required strongPassword min max" data-min="8" data-max="25">
                <div class="error" id="user_passwordError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Confirm Password</label>
                <input type="password" name="confirmPassword"
                    class="form-control"
                    data-validation="required confirmPassword" data-password-id="password">
                <div class="error" id="confirmPasswordError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Profile Picture</label>
                <input type="file" name="profile_img"
                    class="form-control"
                    data-validation="required file filesize" data-filesize="500">
                <div class="error" id="profile_imgError"></div>
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 text-white py-3 rounded-lg text-lg font-bold hover:bg-indigo-700 transition-all">
                Sign Up
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>