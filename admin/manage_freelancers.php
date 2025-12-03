<?php
session_start();
include '../config.php';

$page_title = "Manage Freelancers";
include 'header.php';

// -------------------------------
// DELETE FREELANCER + USER
// -------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $user_id = $_GET['user_id'];
    $freelancer_id = $_GET['freelancer_id'];

    // Delete freelancer first
    $conn->query("DELETE FROM freelancers WHERE freelancer_id = $freelancer_id");

    // Delete user
    $conn->query("DELETE FROM users WHERE user_id = $user_id");

    header("Location: manage_freelancers.php");
    exit;
}

// -------------------------------
// EDIT FORM
// -------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $user_id = $_GET['user_id'];
    $freelancer_id = $_GET['freelancer_id'];

    $query = "
        SELECT u.*, f.*
        FROM users u
        JOIN freelancers f ON u.user_id = f.user_id
        WHERE u.user_id = $user_id";

    $record = $conn->query($query)->fetch_assoc();
}

// -------------------------------
// UPDATE FREELANCER + USER
// -------------------------------
if (isset($_POST['update_freelancer'])) {
    $user_id = $_POST['user_id'];
    $freelancer_id = $_POST['freelancer_id'];

    $username = $_POST['username'];
    $email = $_POST['user_email'];
    $phone = $_POST['user_phone'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $bio = $_POST['bio'];
    $skills = $_POST['skills'];
    $experience_year = $_POST['experience_year'];
    $portfolio_url = $_POST['portfolio_url'];

    // IMAGE UPLOAD
    if (!empty($_FILES['profile_img']['name'])) {
        $filename = time() . "_" . $_FILES['profile_img']['name'];
        move_uploaded_file(
            $_FILES['profile_img']['tmp_name'],
            "../uploads/profile/" . $filename
        );

        $conn->query("UPDATE users SET profile_img = '$filename' WHERE user_id = $user_id");
    }

    // Update users table
    $conn->query("
        UPDATE users
        SET username = '$username',
            user_email = '$email',
            user_phone = '$phone'
        WHERE user_id = $user_id
    ");

    // Update freelancers table
    $conn->query("
        UPDATE freelancers
        SET fname = '$fname',
            lname = '$lname',
            bio = '$bio',
            skills = '$skills',
            experience_year = '$experience_year',
            portfolio_url = '$portfolio_url'
        WHERE freelancer_id = $freelancer_id
    ");

    header("Location: manage_freelancers.php");
    exit;
}

// -------------------------------
// ADD NEW FREELANCER
// -------------------------------
if (isset($_POST['add_freelancer'])) {

    $username = $_POST['username'];
    $email = $_POST['user_email'];
    $phone = $_POST['user_phone'];
    $password = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $bio = $_POST['bio'];
    $skills = $_POST['skills'];
    $experience_year = $_POST['experience_year'];
    $portfolio_url = $_POST['portfolio_url'];

    // Upload profile image
    $filename = "";
    if (!empty($_FILES['profile_img']['name'])) {
        $filename = time() . "_" . $_FILES['profile_img']['name'];
        move_uploaded_file(
            $_FILES['profile_img']['tmp_name'],
            "../uploads/profile/" . $filename
        );
    }

    // Insert into users table
    $conn->query("
        INSERT INTO users (username, user_email, user_phone, role, user_password, profile_img)
        VALUES ('$username', '$email', '$phone', 'Freelancer', '$password', '$filename')
    ");

    $new_user_id = $conn->insert_id;

    // Insert into freelancers table
    $conn->query("
        INSERT INTO freelancers (user_id, fname, lname, bio, skills, experience_year, portfolio_url)
        VALUES ($new_user_id, '$fname', '$lname', '$bio', '$skills', '$experience_year', '$portfolio_url')
    ");

    header("Location: manage_freelancers.php");
    exit;
}
?>

<!-- MAIN PAGE CONTENT -->
<main class="p-6">

    <div class="flex justify-between mb-6">
        <h2 class="text-2xl font-semibold">Manage Freelancers</h2>
        <a href="manage_freelancers.php?action=add" class="bg-indigo-600 text-white px-4 py-2 rounded">
            + Add Freelancer
        </a>
    </div>

    <?php
    // -------------------------------
    // ADD / EDIT FORM UI
    // -------------------------------
    if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')):
        ?>

        <form method="POST" enctype="multipart/form-data"
            class="bg-white shadow rounded-xl p-6 max-w-3xl space-y-6 validateForm">

            <input type="hidden" name="user_id" value="<?= $record['user_id'] ?? '' ?>">
            <input type="hidden" name="freelancer_id" value="<?= $record['freelancer_id'] ?? '' ?>">

            <!-- USERNAME -->
            <div>
                <label class="block font-medium mb-1">Username</label>
                <input type="text" name="username" value="<?= $record['username'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="usernameError"></p>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="block font-medium mb-1">Email</label>
                <input type="email" name="user_email" value="<?= $record['user_email'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required email">
                <p class="error text-red-500 text-sm mt-1" id="user_emailError"></p>
            </div>

            <!-- PHONE -->
            <div>
                <label class="block font-medium mb-1">Phone</label>
                <input type="text" name="user_phone" value="<?= $record['user_phone'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required numeric min" data-min="10">
                <p class="error text-red-500 text-sm mt-1" id="user_phoneError"></p>
            </div>

            <?php if ($_GET['action'] === 'add'): ?>
                <!-- PASSWORD -->
                <div>
                    <label class="block font-medium mb-1">Password</label>
                    <input type="password" name="user_password" class="w-full border px-3 py-2 rounded"
                        data-validation="required strongPassword">
                    <p class="error text-red-500 text-sm mt-1" id="user_passwordError"></p>
                </div>
            <?php endif; ?>

            <!-- PROFILE IMAGE -->
            <div>
                <label class="block font-medium mb-1">Profile Image</label>
                <input type="file" name="profile_img" class="w-full border px-3 py-2 rounded"
                    data-validation="<?= $_GET['action'] === 'add' ? 'required ' : '' ?>file filesize" data-filesize="1024">
                <p class="error text-red-500 text-sm mt-1" id="profile_imgError"></p>

                <?php if (!empty($record['profile_img'])): ?>
                    <img src="../uploads/profile/<?= $record['profile_img']; ?>" class="w-20 h-20 rounded-full mt-2">
                <?php endif; ?>
            </div>

            <!-- FIRST NAME -->
            <div>
                <label class="block font-medium mb-1">First Name</label>
                <input type="text" name="fname" value="<?= $record['fname'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="fnameError"></p>
            </div>

            <!-- LAST NAME -->
            <div>
                <label class="block font-medium mb-1">Last Name</label>
                <input type="text" name="lname" value="<?= $record['lname'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="lnameError"></p>
            </div>

            <!-- BIO -->
            <div>
                <label class="block font-medium mb-1">Bio</label>
                <textarea name="bio" class="w-full border px-3 py-2 rounded"
                    data-validation="required"><?= $record['bio'] ?? '' ?></textarea>
                <p class="error text-red-500 text-sm mt-1" id="bioError"></p>
            </div>

            <!-- SKILLS -->
            <div>
                <label class="block font-medium mb-1">Skills</label>
                <input type="text" name="skills" value="<?= $record['skills'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required">
                <p class="error text-red-500 text-sm mt-1" id="skillsError"></p>
            </div>

            <!-- EXPERIENCE -->
            <div>
                <label class="block font-medium mb-1">Experience (years)</label>
                <input type="text" name="experience_year" value="<?= $record['experience_year'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required numeric">
                <p class="error text-red-500 text-sm mt-1" id="experience_yearError"></p>
            </div>

            <!-- PORTFOLIO -->
            <div>
                <label class="block font-medium mb-1">Portfolio URL</label>
                <input type="text" name="portfolio_url" value="<?= $record['portfolio_url'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required">
                <p class="error text-red-500 text-sm mt-1" id="portfolio_urlError"></p>
            </div>

            <!-- BUTTONS -->
            <div class="flex items-center gap-4 mt-6">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded">
                    <?= $_GET['action'] === 'edit' ? "Update Freelancer" : "Add Freelancer" ?>
                </button>
                <a href="manage_freelancers.php" class="text-indigo-600 hover:underline">Back</a>
            </div>

            <input type="hidden" name="<?= $_GET['action'] === 'edit' ? "update_freelancer" : "add_freelancer" ?>"
                value="1">
        </form>

        <?php
        // END FORM
    else:
        ?>

        <!-- ---------------- LIST SECTION ---------------- -->
        <?php
        $result = $conn->query("
        SELECT u.*, f.*
        FROM users u
        JOIN freelancers f ON u.user_id = f.user_id
        WHERE role = 'freelancer'
        ORDER BY f.freelancer_id DESC
    ");
        ?>

        <table class="min-w-full bg-white shadow rounded-xl overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Profile</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Skills</th>
                    <th class="p-3">Experience</th>
                    <th class="p-3">Joined</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3">
                            <img src="../uploads/profile/<?= $row['profile_img']; ?>"
                                class="w-12 h-12 rounded-full object-cover">
                        </td>

                        <td class="p-3"><?= $row['fname'] . " " . $row['lname']; ?></td>
                        <td class="p-3"><?= $row['user_email']; ?></td>
                        <td class="p-3"><?= $row['user_phone']; ?></td>
                        <td class="p-3"><?= $row['skills']; ?></td>
                        <td class="p-3"><?= $row['experience_year']; ?> years</td>
                        <td class="p-3 text-gray-600">
                            <?= date("d M Y", strtotime($row['created_at'])); ?>
                        </td>

                        <td class="p-3 flex gap-4">
                            <a href="manage_freelancers.php?action=edit&user_id=<?= $row['user_id']; ?>&freelancer_id=<?= $row['freelancer_id']; ?>"
                                class="text-indigo-600 hover:underline">
                                Edit
                            </a>

                            <a href="manage_freelancers.php?action=delete&user_id=<?= $row['user_id']; ?>&freelancer_id=<?= $row['freelancer_id']; ?>"
                                onclick="return confirm('Delete freelancer?')" class="text-red-600 hover:underline">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php endif; ?>

    <script src="../assets/jquery.min.js"></script>
    <script src="../assets/validate.js?v=<?= time(); ?>"></script>
</main>

<?php include 'footer.php'; ?>