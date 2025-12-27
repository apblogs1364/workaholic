<?php
session_start();
include 'config.php';

/* -------------------------------------------------
   ACCESS CHECK
------------------------------------------------- */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$success = "";
$error = "";

/* -------------------------------------------------
   FETCH BASE USER ROW
------------------------------------------------- */
$stmt = $conn->prepare("SELECT user_id, username, user_email, role, user_phone, profile_img, created_at 
                        FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    header("Location: logout.php");
    exit;
}

$role               = $user['role'];
$user_email         = $user['user_email'];
$user_phone         = $user['user_phone'];
$profile_img        = $user['profile_img'] ?: "Images/default.png";
$username_from_users = $user['username'] ?? "";

/* -------------------------------------------------
   FETCH ROLE TABLE ROW
------------------------------------------------- */
$role_row_exists = false;

if ($role === "freelancer") {

    $stmt = $conn->prepare("SELECT * FROM freelancers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $freelancer = $stmt->get_result()->fetch_assoc();

    if ($freelancer) {
        $role_row_exists = true;
        $fname            = $freelancer['fname'];
        $lname            = $freelancer['lname'];
        $bio              = $freelancer['bio'];
        $skills           = $freelancer['skills'];
        $experience_year  = $freelancer['experience_year'];
        $portfolio_url    = $freelancer['portfolio_url'];
    } else {
        $fname = $lname = $bio = $skills = $experience_year = $portfolio_url = "";
    }
} else {

    $stmt = $conn->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();

    if ($company) {
        $role_row_exists = true;
        $company_name        = $company['company_name'];
        $company_address     = $company['company_address'];
        $company_description = $company['company_description'];
        $company_website     = $company['company_website'];
        $category_id       = (int)$company['category_id'];
    } else {
        $company_name = $company_address = $company_description =
            $company_website = $category_id = "";
    }
}

/* -------------------------------------------------
   FORM SUBMISSION
------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {

    $new_email  = trim($_POST['user_email']);
    $new_phone  = trim($_POST['user_phone']);

    /* --------- VALIDATION (Same style as manage_admins.php) --------- */
    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email.";
    } elseif (!preg_match('/^[0-9]{10}$/', $new_phone)) {
        $error = "Phone must be 10 digits.";
    } else {

        /* --------- IMAGE UPLOAD --------- */
        if (!empty($_FILES['profile_img']['name'])) {

            $allowed = ["jpg", "jpeg", "png"];
            $ext = strtolower(pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Only JPG and PNG allowed.";
            } else {
                $uploadDir = "uploads/profile/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = time() . "_" . basename($_FILES['profile_img']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetPath)) {
                    $profile_img = $targetPath;
                } else {
                    $error = "Image upload failed.";
                }
            }
        }

        if ($error === "") {

            /* --------- UPDATE USERS TABLE --------- */
            $u = $conn->prepare("UPDATE users SET user_email=?, user_phone=?, profile_img=? WHERE user_id=?");
            $u->bind_param("sssi", $new_email, $new_phone, $profile_img, $user_id);
            $u->execute();

            /* --------- ROLE-SPECIFIC UPDATE --------- */
            if ($role === "freelancer") {

                $f_fname = trim($_POST['fname']);
                $f_lname = trim($_POST['lname']);
                $f_bio   = trim($_POST['bio']);
                $f_skill = trim($_POST['skills']);
                $f_exp   = trim($_POST['experience_year']);
                $f_port  = trim($_POST['portfolio_url']);

                $q = $conn->prepare("UPDATE freelancers 
                                    SET fname=?, lname=?, bio=?, skills=?, experience_year=?, portfolio_url=? 
                                    WHERE user_id=?");
                $q->bind_param("ssssssi", $f_fname, $f_lname, $f_bio, $f_skill, $f_exp, $f_port, $user_id);
                $q->execute();

                /* Update username based on First Name */
                if (!empty($f_fname)) {
                    $nameUpdate = $conn->prepare("UPDATE users SET username=? WHERE user_id=?");
                    $nameUpdate->bind_param("si", $f_fname, $user_id);
                    $nameUpdate->execute();
                }
            } else {

                $c_name    = trim($_POST['company_name']);
                $c_add     = trim($_POST['company_address']);
                $c_desc    = trim($_POST['company_description']);
                $c_web     = trim($_POST['company_website']);
                $c_type    = (int)trim($_POST['category_id']);

                $q = $conn->prepare("UPDATE companies 
                        SET company_name=?, company_address=?, company_description=?, company_website=?, category_id=? 
                        WHERE user_id=?");
                $q->bind_param("ssssii", $c_name, $c_add, $c_desc, $c_web, $c_type, $user_id);
                $q->execute();

                /* Update username based on Company Name */
                if (!empty($c_name)) {
                    $nameUpdate = $conn->prepare("UPDATE users SET username=? WHERE user_id=?");
                    $nameUpdate->bind_param("si", $c_name, $user_id);
                    $nameUpdate->execute();
                }
            }

            $success = "Profile updated successfully.";
        }
    }
}

/* -------------------------------------------------
   VIEW HTML PART
------------------------------------------------- */
$page_title = "Profile";
include "header.php";
?>

<style>
    .error {
        color: #ef4444;
        font-size: 14px;
        margin-top: 4px
    }

    .success {
        background: #ecfdf5;
        color: #065f46;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 12px
    }

    .form-control {
        border: 1px solid #bbb;
        padding: 8px;
        width: 100%;
        border-radius: 8px
    }
</style>

<script src="assets/jquery.min.js"></script>
<script src="assets/validate.js"></script>

<div class="max-w-4xl mx-auto mt-12 mb-12">
    <div class="bg-white shadow-xl rounded-2xl p-8">

        <!-- PROFILE HEADER -->
        <div class="flex items-center gap-6">
            <img src="<?php echo $profile_img ?>" class="w-28 h-28 rounded-full object-cover border-4 border-indigo-500 shadow">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">
                    <?php
                    if ($role === "freelancer") {
                        echo trim("$fname $lname") ?: $username_from_users;
                    } else {
                        echo $company_name ?: $username_from_users;
                    }
                    ?>
                </h2>

                <p class="text-gray-600">
                    <?php echo ($role === "freelancer") ? $bio : $company_description; ?>
                </p>
            </div>
        </div>

        <hr class="my-6">

        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error mb-4"><?= $error ?></div><?php endif; ?>

        <!-- DISPLAY INFO -->
        <div id="profile-info">

            <h3 class="text-xl font-semibold mb-4">About</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                <p><strong>Email:</strong> <?= $user_email ?></p>
                <p><strong>Phone:</strong> <?= $user_phone ?></p>

                <?php if ($role === "freelancer"): ?>

                    <p><strong>Skills:</strong> <?= $skills ?: "Not added" ?></p>
                    <p><strong>Experience:</strong> <?= $experience_year ?> Years</p>
                    <p><strong>Portfolio:</strong> <?= $portfolio_url ?: "Not added" ?></p>

                <?php else: ?>

                    <p><strong>Address:</strong> <?= $company_address ?: "Not added" ?></p>
                    <?php
                    $type_name = "Not added";
                    if ($category_id) {
                        $catRes = $conn->prepare("SELECT category_name FROM categories WHERE category_id=?");
                        $catRes->bind_param("i", $category_id);
                        $catRes->execute();
                        $catRow = $catRes->get_result()->fetch_assoc();
                        if ($catRow) $type_name = $catRow['category_name'];
                    }
                    ?>
                    <p><strong>Type:</strong> <?= $type_name ?></p>
                    <p><strong>Website:</strong> <?= $company_website ?: "Not added" ?></p>

                <?php endif; ?>
            </div>

            <div class="mt-6 flex gap-4">
                <!-- Edit Button -->
                <button onclick="toggleEdit()" class="px-5 py-2 bg-indigo-600 text-white rounded-lg">
                    Edit Profile
                </button>

                <!-- Manage Jobs Button for Company -->
                <?php if ($role === 'company'): ?>
                    <a href="job.php"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        Manage Jobs
                    </a>
                    <a href="proposals_receive.php"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        Proposals Receive
                    </a>
                    <a href="contract.php"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        Contracts
                    </a>
                <?php elseif ($role === 'freelancer'): ?>
                    <a href="view_proposal.php"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        View Proposals
                    </a>
                    <a href="contract.php"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl shadow hover:bg-green-700">
                        Contracts
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- EDIT FORM -->
        <div id="edit-form" class="hidden mt-8">
            <h3 class="text-xl font-semibold mb-4">Edit Profile</h3>

            <form method="POST" enctype="multipart/form-data" id="profileForm">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- EMAIL -->
                    <div>
                        <label>Email</label>
                        <input type="email" name="user_email" value="<?= $user_email ?>" class="form-control"
                            data-validation="required email">
                    </div>

                    <!-- PHONE -->
                    <div>
                        <label>Phone</label>
                        <input type="text" name="user_phone" value="<?= $user_phone ?>" class="form-control"
                            data-validation="required numeric min max" data-min="10" data-max="10">
                    </div>

                    <!-- FREELANCER FIELDS -->
                    <?php if ($role === "freelancer"): ?>

                        <div>
                            <label>First Name</label>
                            <input type="text" name="fname" value="<?= $fname ?>" class="form-control">
                        </div>

                        <div>
                            <label>Last Name</label>
                            <input type="text" name="lname" value="<?= $lname ?>" class="form-control">
                        </div>

                        <div class="md:col-span-2">
                            <label>Bio</label>
                            <textarea name="bio" class="form-control"><?= $bio ?></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label>Skills</label>
                            <input type="text" name="skills" value="<?= $skills ?>" class="form-control">
                        </div>

                        <div>
                            <label>Experience</label>
                            <input type="number" name="experience_year" value="<?= $experience_year ?>" class="form-control">
                        </div>

                        <div>
                            <label>Portfolio</label>
                            <input type="text" name="portfolio_url" value="<?= $portfolio_url ?>" class="form-control">
                        </div>

                    <?php else: ?>

                        <div class="md:col-span-2">
                            <label>Company Name</label>
                            <input type="text" name="company_name" value="<?= $company_name ?>" class="form-control">
                        </div>

                        <div class="md:col-span-2">
                            <label>Address</label>
                            <input type="text" name="company_address" value="<?= $company_address ?>" class="form-control">
                        </div>

                        <div class="md:col-span-2">
                            <label>Description</label>
                            <textarea name="company_description" class="form-control"><?= $company_description ?></textarea>
                        </div>

                        <div>
                            <label>Website</label>
                            <input type="text" name="company_website" value="<?= $company_website ?>" class="form-control">
                        </div>

                        <?php
                        // FETCH ALL CATEGORIES FOR DROPDOWN
                        $catQuery = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC");
                        ?>

                        <div>
                            <label>Business Type</label>
                            <select name="category_id"
                                class="form-control"
                                data-validation="required">
                                <option value="">-- Select Business Type --</option>

                                <?php while ($cat = $catQuery->fetch_assoc()): ?>
                                    <option value="<?= $cat['category_id']; ?>"
                                        <?= ($category_id == $cat['category_id']) ? "selected" : "" ?>>
                                        <?= $cat['category_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                    <?php endif; ?>

                    <!-- IMAGE -->
                    <div class="md:col-span-2">
                        <label>Profile Image</label>
                        <input type="file" name="profile_img" class="form-control">
                    </div>

                </div>

                <div class="mt-6">
                    <button type="submit" name="save_changes"
                        class="px-8 py-3 bg-indigo-600 text-white rounded-xl">
                        Save Changes
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
    function toggleEdit() {
        $("#profile-info").hide();
        $("#edit-form").removeClass("hidden");
    }

    $(document).ready(function() {
        $("#profileForm").validate({
            errorClass: "error"
        });
    });
</script>
<script>
    // Hide success message after 3 seconds
    setTimeout(function() {
        const successBox = document.querySelector(".success");
        if (successBox) {
            successBox.style.transition = "opacity 0.5s";
            successBox.style.opacity = "0";
            setTimeout(() => successBox.remove(), 500);
        }
    }, 2000);
</script>


<?php include "footer.php"; ?>