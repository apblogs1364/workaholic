<?php
session_start();
include 'config.php';

// ----------------------- ACCESS CHECK -----------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = "";
$error = "";

// ----------------------- FETCH BASE USER DATA -----------------------
$stmt = $conn->prepare("SELECT user_id, username, user_email, role, user_phone, profile_img, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    header("Location: logout.php");
    exit;
}

$role = $user['role']; // 'freelancer' or 'company'
$user_email = $user['user_email'];
$user_phone = $user['user_phone'];
$profile_img = $user['profile_img'] ?: "Images/default.png";
$username_from_users = $user['username'] ?? "";

// ----------------------- FETCH ROLE-SPECIFIC ROW (EXISTS BUT MAY BE EMPTY) -----------------------
$role_row_exists = false;
if ($role === "freelancer") {
    $stmt = $conn->prepare("SELECT * FROM freelancers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $freelancer = $stmt->get_result()->fetch_assoc();
    if ($freelancer) {
        $role_row_exists = true;
        $fname = $freelancer['fname'] ?? "";
        $lname = $freelancer['lname'] ?? "";
        $bio = $freelancer['bio'] ?? "";
        $skills = $freelancer['skills'] ?? "";
        $experience_year = $freelancer['experience_year'] ?? "";
        $portfolio_url = $freelancer['portfolio_url'] ?? "";
    } else {
        // Defensive fallback (shouldn't happen if your registration creates a row)
        $role_row_exists = false;
        $fname = $lname = $bio = $skills = $experience_year = $portfolio_url = "";
    }
} else { // company
    $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();
    if ($company) {
        $role_row_exists = true;
        $company_name = $company['company_name'] ?? "";
        $company_address = $company['company_address'] ?? "";
        $company_description = $company['company_description'] ?? "";
        $company_website = $company['company_website'] ?? "";
        $business_type = $company['business_type'] ?? "";
    } else {
        // Defensive fallback
        $role_row_exists = false;
        $company_name = $company_address = $company_description = $company_website = $business_type = "";
    }
}

// ----------------------- HANDLE FORM SUBMISSION -----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {

    // Basic cleaning
    $new_email = trim($_POST['user_email'] ?? $user_email);
    $new_phone = trim($_POST['user_phone'] ?? $user_phone);

    // Server-side minimal validation
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL) === false) {
        $error = "Please enter a valid email.";
    } elseif (!preg_match('/^\d{10}$/', $new_phone)) {
        $error = "Please enter a valid 10-digit phone number.";
    } else {

        // Handle image upload if provided
        if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] !== UPLOAD_ERR_NO_FILE) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $uploadDir = __DIR__ . '/uploads/profile/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $file = $_FILES['profile_img'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Profile image must be JPG or PNG.";
            } elseif ($file['size'] > 3 * 1024 * 1024) {
                $error = "Profile image must be less than 3MB.";
            } else {
                $newFileName = time() . "_" . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', basename($file['name']));
                $target = $uploadDir . $newFileName;
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    // store web path
                    $profile_img = 'uploads/profile/' . $newFileName;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if ($error === "") {
            // 1) Update users table (also update username if role fields provided)
            // We'll update username later based on role-specific fields

            $u_stmt = $conn->prepare("UPDATE users SET user_email = ?, user_phone = ?, profile_img = ? WHERE user_id = ?");
            $u_stmt->bind_param("sssi", $new_email, $new_phone, $profile_img, $user_id);
            $u_stmt->execute();

            // 2) Role specific update (row exists but may be empty). Use UPDATE. If UPDATE affects 0 rows (row missing), attempt INSERT.
            if ($role === "freelancer") {
                $f_fname = trim($_POST['fname'] ?? '');
                $f_lname = trim($_POST['lname'] ?? '');
                $f_bio = trim($_POST['bio'] ?? '');
                $f_skills = trim($_POST['skills'] ?? '');
                $f_experience = trim($_POST['experience_year'] ?? '');
                $f_portfolio = trim($_POST['portfolio_url'] ?? '');

                if ($role_row_exists) {
                    $f_stmt = $conn->prepare("UPDATE freelancers SET fname=?, lname=?, bio=?, skills=?, experience_year=?, portfolio_url=? WHERE user_id=?");
                    $f_stmt->bind_param("ssssssi", $f_fname, $f_lname, $f_bio, $f_skills, $f_experience, $f_portfolio, $user_id);
                    $f_stmt->execute();
                    // if the update affected 0 rows (rare), as fallback try insert
                    if ($f_stmt->affected_rows === 0) {
                        $ins = $conn->prepare("INSERT INTO freelancers (user_id, fname, lname, bio, skills, experience_year, portfolio_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                        $ins->bind_param("issssss", $user_id, $f_fname, $f_lname, $f_bio, $f_skills, $f_experience, $f_portfolio);
                        $ins->execute();
                        $role_row_exists = true;
                    }
                } else {
                    // defensive insert
                    $f_stmt = $conn->prepare("INSERT INTO freelancers (user_id, fname, lname, bio, skills, experience_year, portfolio_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $f_stmt->bind_param("issssss", $user_id, $f_fname, $f_lname, $f_bio, $f_skills, $f_experience, $f_portfolio);
                    $f_stmt->execute();
                    $role_row_exists = true;
                }

                // Update username in users table to fname if fname not empty
                if ($f_fname !== '') {
                    $u2 = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                    $u2->bind_param("si", $f_fname, $user_id);
                    $u2->execute();
                    $_SESSION['username'] = $f_fname;
                }
            } else { // company
                $c_name = trim($_POST['company_name'] ?? '');
                $c_address = trim($_POST['company_address'] ?? '');
                $c_description = trim($_POST['company_description'] ?? '');
                $c_website = trim($_POST['company_website'] ?? '');
                $c_business_type = trim($_POST['business_type'] ?? '');

                if ($role_row_exists) {
                    $c_stmt = $conn->prepare("UPDATE companies SET company_name=?, company_address=?, company_description=?, company_website=?, business_type=? WHERE user_id=?");
                    $c_stmt->bind_param("sssssi", $c_name, $c_address, $c_description, $c_website, $c_business_type, $user_id);
                    $c_stmt->execute();
                    if ($c_stmt->affected_rows === 0) {
                        $ins = $conn->prepare("INSERT INTO companies (user_id, company_name, company_address, company_description, company_website, business_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                        $ins->bind_param("isssss", $user_id, $c_name, $c_address, $c_description, $c_website, $c_business_type);
                        $ins->execute();
                        $role_row_exists = true;
                    }
                } else {
                    // defensive insert
                    $c_stmt = $conn->prepare("INSERT INTO companies (user_id, company_name, company_address, company_description, company_website, business_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $c_stmt->bind_param("isssss", $user_id, $c_name, $c_address, $c_description, $c_website, $c_business_type);
                    $c_stmt->execute();
                    $role_row_exists = true;
                }

                // Update username in users table to company_name if provided
                if ($c_name !== '') {
                    $u2 = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                    $u2->bind_param("si", $c_name, $user_id);
                    $u2->execute();
                    $_SESSION['username'] = $c_name;
                }
            }

            // refresh data displayed
            $success = "Profile updated successfully.";

            // fetch updated base user
            $stmt = $conn->prepare("SELECT user_id, username, user_email, role, user_phone, profile_img, created_at FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $user_email = $user['user_email'];
            $user_phone = $user['user_phone'];
            $profile_img = $user['profile_img'] ?: "Images/default.png";
            $username_from_users = $user['username'] ?? $username_from_users;

            // refetch role specifics (safe to fetch again)
            if ($role === "freelancer") {
                $stmt = $conn->prepare("SELECT * FROM freelancers WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $freelancer = $stmt->get_result()->fetch_assoc();
                $fname = $freelancer['fname'] ?? "";
                $lname = $freelancer['lname'] ?? "";
                $bio = $freelancer['bio'] ?? "";
                $skills = $freelancer['skills'] ?? "";
                $experience_year = $freelancer['experience_year'] ?? "";
                $portfolio_url = $freelancer['portfolio_url'] ?? "";
            } else {
                $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $company = $stmt->get_result()->fetch_assoc();
                $company_name = $company['company_name'] ?? "";
                $company_address = $company['company_address'] ?? "";
                $company_description = $company['company_description'] ?? "";
                $company_website = $company['company_website'] ?? "";
                $business_type = $company['business_type'] ?? "";
            }
        }
    }
}

// Page title and header include (no output before this in earlier code)
$page_title = "Profile";
include 'header.php';
?>

<!-- styles & validation scripts (same as you requested) -->
<style>
    .error {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 4px;
    }

    .form-control {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.6rem 0.85rem;
        width: 100%;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        outline: none;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
        display: block;
    }

    .success {
        color: #065f46;
        background: #ecfdf5;
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        display: inline-block;
    }
</style>

<script src="assets/jquery.min.js"></script>
<script src="assets/validate.js"></script>

<!-- MAIN -->
<div class="max-w-4xl mx-auto mt-12 mb-12">
    <div class="bg-white shadow-xl rounded-2xl p-8">

        <div class="flex items-center gap-6">
            <img src="<?php echo htmlspecialchars($profile_img); ?>" class="w-28 h-28 rounded-full object-cover border-4 border-indigo-500 shadow">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">
                    <?php
                    // Show username: prefer fname/company_name if present, otherwise show users.username
                    if ($role === 'freelancer') {
                        $display_name = trim(($fname ?? '') . ' ' . ($lname ?? ''));
                        if ($display_name === '') $display_name = $username_from_users;
                        echo htmlspecialchars($display_name);
                    } else {
                        $display_name = $company_name ?? '';
                        if (trim($display_name) === '') $display_name = $username_from_users;
                        echo htmlspecialchars($display_name);
                    }
                    ?>
                </h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($bio ?? $company_description ?? ''); ?></p>
            </div>
        </div>

        <hr class="my-6">

        <?php if ($success && ($_POST['show_success'] ?? '0') === '1'): ?>
            <div id="successMsg" class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="error mb-4"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- PROFILE INFO -->
        <div id="profile-info">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">About</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_phone); ?></p>

                <?php if ($role === 'freelancer'): ?>
                    <p><strong>Skills:</strong> <?php echo htmlspecialchars($skills ?: 'Not added yet'); ?></p>
                    <p><strong>Experience:</strong> <?php echo htmlspecialchars($experience_year ?: 'Not added'); ?> Years</p>
                    <p><strong>Portfolio:</strong> <?php echo htmlspecialchars($portfolio_url ?: 'Not added'); ?></p>
                <?php else: ?>
                    <p><strong>Company Address:</strong> <?php echo htmlspecialchars($company_address ?: 'Not added'); ?></p>
                    <p><strong>Business Type:</strong> <?php echo htmlspecialchars($business_type ?: 'Not added'); ?></p>
                    <p><strong>Website:</strong> <?php echo htmlspecialchars($company_website ?: 'Not added'); ?></p>
                <?php endif; ?>
            </div>

            <div class="mt-8">
                <button onclick="toggleEdit()" class="px-6 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">Edit Profile</button>
            </div>
        </div>

        <!-- EDIT FORM -->
        <div id="edit-form" class="hidden mt-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Profile</h3>

            <form method="POST" enctype="multipart/form-data" id="profileForm">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- hides success message -->
                    <input type="hidden" name="show_success" id="show_success" value="0">

                    <!-- Email -->
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="user_email" value="<?php echo htmlspecialchars($user_email); ?>" class="form-control" data-validation="required email">
                        <div class="error" id="user_emailError"></div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="user_phone" value="<?php echo htmlspecialchars($user_phone); ?>" class="form-control" data-validation="required numeric min max" data-min="10" data-max="10">
                        <div class="error" id="user_phoneError"></div>
                    </div>

                    <?php if ($role === 'freelancer'): ?>

                        <div>
                            <label class="form-label">First Name</label>
                            <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>" class="form-control" data-validation="required alpha min max" data-min="2" data-max="25">
                            <div class="error" id="fnameError"></div>
                        </div>

                        <div>
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lname" value="<?php echo htmlspecialchars($lname); ?>" class="form-control" data-validation="alpha min max" data-min="0" data-max="25">
                            <div class="error" id="lnameError"></div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Bio</label>
                            <textarea name="bio" class="form-control" rows="3" data-validation="min max" data-min="0" data-max="500"><?php echo htmlspecialchars($bio); ?></textarea>
                            <div class="error" id="bioError"></div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Skills</label>
                            <input type="text" name="skills" value="<?php echo htmlspecialchars($skills); ?>" class="form-control">
                            <div class="error" id="skillsError"></div>
                        </div>

                        <div>
                            <label class="form-label">Experience (Years)</label>
                            <input type="number" name="experience_year" value="<?php echo htmlspecialchars($experience_year); ?>" class="form-control" data-validation="numeric">
                            <div class="error" id="experienceError"></div>
                        </div>

                        <div>
                            <label class="form-label">Portfolio URL</label>
                            <input type="text" name="portfolio_url" value="<?php echo htmlspecialchars($portfolio_url); ?>" class="form-control">
                            <div class="error" id="portfolioError"></div>
                        </div>

                    <?php else: // company 
                    ?>

                        <div class="md:col-span-2">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" value="<?php echo htmlspecialchars($company_name); ?>" class="form-control" data-validation="required">
                            <div class="error" id="companyNameError"></div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Company Address</label>
                            <input type="text" name="company_address" value="<?php echo htmlspecialchars($company_address); ?>" class="form-control" data-validation="required">
                            <div class="error" id="companyAddressError"></div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Description</label>
                            <textarea name="company_description" class="form-control" rows="3" data-validation="required"><?php echo htmlspecialchars($company_description); ?></textarea>
                            <div class="error" id="companyDescError"></div>
                        </div>

                        <div>
                            <label class="form-label">Website</label>
                            <input type="text" name="company_website" value="<?php echo htmlspecialchars($company_website); ?>" class="form-control">
                            <div class="error" id="companyWebsiteError"></div>
                        </div>

                        <div>
                            <label class="form-label">Business Type</label>
                            <input type="text" name="business_type" value="<?php echo htmlspecialchars($business_type); ?>" class="form-control">
                            <div class="error" id="businessTypeError"></div>
                        </div>

                    <?php endif; ?>

                    <div class="md:col-span-2">
                        <label class="form-label">Profile Image</label>
                        <input type="file" name="profile_img" class="form-control" data-validation="mime size" data-mime="jpg,png,jpeg" data-size="3">
                        <div class="error" id="profileImageError"></div>
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit" name="save_changes" class="px-8 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 shadow">Save Changes</button>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
    function toggleEdit() {
        document.getElementById('profile-info').classList.toggle('hidden');
        document.getElementById('edit-form').classList.toggle('hidden');
    }
</script>
<script>
    const profileForm = document.getElementById('profileForm');
    profileForm.addEventListener('submit', function() {
        document.getElementById('show_success').value = "1";
    });
</script>
<script>
    setTimeout(() => {
        const msg = document.getElementById("successMsg");
        if (msg) {
            msg.style.opacity = "0";
            msg.style.transition = "opacity 0.6s ease";
            setTimeout(() => msg.remove(), 600);
        }
    }, 3000);
</script>

<?php include 'footer.php'; ?>