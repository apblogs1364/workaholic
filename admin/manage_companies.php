<?php
session_start();
include '../config.php';
include 'header.php';

$page_title = "Manage Companies";

// -------------------------------
// DELETE COMPANY + USER
// -------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $user_id = $_GET['user_id'];
    $company_id = $_GET['company_id'];

    // delete company first
    $conn->query("DELETE FROM companies WHERE company_id = $company_id");

    // delete user
    $conn->query("DELETE FROM users WHERE user_id = $user_id");

    header("Location: manage_companies.php");
    exit;
}

// -------------------------------
// EDIT FORM
// -------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $user_id = $_GET['user_id'];
    $company_id = $_GET['company_id'];

    $query = "
        SELECT u.*, c.*
        FROM users u
        JOIN companies c ON u.user_id = c.user_id
        WHERE u.user_id = $user_id
    ";

    $record = $conn->query($query)->fetch_assoc();
}

// -------------------------------
// UPDATE COMPANY + USER
// -------------------------------
if (isset($_POST['update_company'])) {
    $user_id = $_POST['user_id'];
    $company_id = $_POST['company_id'];

    $username = $_POST['username'];
    $email = $_POST['user_email'];
    $phone = $_POST['user_phone'];
    $password = $_POST['user_password'];
    $company_name = $_POST['company_name'];
    $company_address = $_POST['company_address'];
    $company_description = $_POST['company_description'];
    $company_website = $_POST['company_website'];
    $business_type = $_POST['business_type'];

    // IMAGE UPLOAD
    if (!empty($_FILES['profile_img']['name'])) {
        $filename = time() . "_" . $_FILES['profile_img']['name'];
        move_uploaded_file($_FILES['profile_img']['tmp_name'], "../uploads/profile/" . $filename);
        $conn->query("UPDATE users SET profile_img = '$filename' WHERE user_id = $user_id");
    }

    // update users table
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET username='$username', user_email='$email', user_phone='$phone', user_password='$password_hash' WHERE user_id=$user_id");
    } else {
        $conn->query("UPDATE users SET username='$username', user_email='$email', user_phone='$phone' WHERE user_id=$user_id");
    }

    // update companies table
    $conn->query("
        UPDATE companies
        SET company_name='$company_name',
            company_address='$company_address',
            company_description='$company_description',
            company_website='$company_website',
            business_type='$business_type'
        WHERE company_id=$company_id
    ");

    header("Location: manage_companies.php");
    exit;
}

// -------------------------------
// ADD NEW COMPANY
// -------------------------------
if (isset($_POST['add_company'])) {

    $username = $_POST['username'];
    $email = $_POST['user_email'];
    $phone = $_POST['user_phone'];
    $password_hash = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
    $company_name = $_POST['company_name'];
    $company_address = $_POST['company_address'];
    $company_description = $_POST['company_description'];
    $company_website = $_POST['company_website'];
    $business_type = $_POST['business_type'];

    // Upload profile image
    $filename = "";
    if (!empty($_FILES['profile_img']['name'])) {
        $filename = time() . "_" . $_FILES['profile_img']['name'];
        move_uploaded_file($_FILES['profile_img']['tmp_name'], "../uploads/profile/" . $filename);
    }

    // insert into users table
    $conn->query("INSERT INTO users (username, user_email, role, user_phone, user_password, profile_img, created_at) VALUES ('$username', '$email', 'Company', '$phone', '$password_hash', '$filename', NOW())");
    $new_user_id = $conn->insert_id;

    // insert into companies table
    $conn->query("INSERT INTO companies (user_id, company_name, company_address, company_description, company_website, business_type, created_at) VALUES ($new_user_id, '$company_name', '$company_address', '$company_description', '$company_website', '$business_type', NOW())");

    header("Location: manage_companies.php");
    exit;
}
?>

<main class="p-6">

    <div class="flex justify-between mb-6">
        <h2 class="text-2xl font-semibold">Manage Companies</h2>
        <a href="manage_companies.php?action=add" class="bg-indigo-600 text-white px-4 py-2 rounded">
            + Add Company
        </a>
    </div>

    <?php if (isset($_GET['action']) && ($_GET['action'] === 'add' || $_GET['action'] === 'edit')): ?>

        <form method="POST" enctype="multipart/form-data"
            class="bg-white shadow rounded-xl p-6 max-w-3xl space-y-6 validateForm">

            <input type="hidden" name="user_id" value="<?= $record['user_id'] ?? '' ?>">
            <input type="hidden" name="company_id" value="<?= $record['company_id'] ?? '' ?>">

            <!-- USER FIELDS -->
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?= $record['username'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="usernameError"></p>
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="user_email" value="<?= $record['user_email'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required email">
                <p class="error text-red-500 text-sm mt-1" id="user_emailError"></p>
            </div>
            <div>
                <label>Phone</label>
                <input type="text" name="user_phone" value="<?= $record['user_phone'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required numeric min" data-min="10">
                <p class="error text-red-500 text-sm mt-1" id="user_phoneError"></p>
            </div>
            <?php if ($_GET['action'] === 'add'): ?>
                <div>
                    <label>Password</label>
                    <input type="password" name="user_password" class="w-full border px-3 py-2 rounded"
                        data-validation="required strongPassword">
                    <p class="error text-red-500 text-sm mt-1" id="user_passwordError"></p>
                </div>
            <?php else: ?>
                <div>
                    <label>Password (leave blank to keep current)</label>
                    <input type="password" name="user_password" class="w-full border px-3 py-2 rounded">
                </div>
            <?php endif; ?>
            <div>
                <label>Profile Image</label>
                <input type="file" name="profile_img" class="w-full border px-3 py-2 rounded"
                    data-validation="<?= $_GET['action'] === 'add' ? 'required ' : '' ?>file filesize" data-filesize="1024">
                <p class="error text-red-500 text-sm mt-1" id="profile_imgError"></p>
                <?php if (!empty($record['profile_img'])): ?>
                    <img src="../uploads/profile/<?= $record['profile_img']; ?>" class="w-20 h-20 mt-2 rounded-full">
                <?php endif; ?>
            </div>

            <!-- COMPANY FIELDS -->
            <div>
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= $record['company_name'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required">
                <p class="error text-red-500 text-sm mt-1" id="company_nameError"></p>
            </div>
            <div>
                <label>Company Address</label>
                <textarea name="company_address" class="w-full border px-3 py-2 rounded"
                    data-validation="required"><?= $record['company_address'] ?? '' ?></textarea>
                <p class="error text-red-500 text-sm mt-1" id="company_addressError"></p>
            </div>
            <div>
                <label>Description</label>
                <textarea name="company_description" class="w-full border px-3 py-2 rounded"
                    data-validation="required"><?= $record['company_description'] ?? '' ?></textarea>
                <p class="error text-red-500 text-sm mt-1" id="company_descriptionError"></p>
            </div>
            <div>
                <label>Website</label>
                <input type="text" name="company_website" value="<?= $record['company_website'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required">
                <p class="error text-red-500 text-sm mt-1" id="company_websiteError"></p>
            </div>
            <div>
                <label>Business Type</label>
                <input type="text" name="business_type" value="<?= $record['business_type'] ?? '' ?>"
                    class="w-full border px-3 py-2 rounded" data-validation="required">
                <p class="error text-red-500 text-sm mt-1" id="business_typeError"></p>
            </div>

            <div class="flex items-center gap-4 mt-6">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded">
                    <?= $_GET['action'] === 'edit' ? "Update Company" : "Add Company" ?>
                </button>
                <a href="manage_companies.php" class="text-indigo-600 hover:underline">Back</a>
            </div>

            <input type="hidden" name="<?= $_GET['action'] === 'edit' ? "update_company" : "add_company" ?>" value="1">
        </form>

    <?php else: ?>

        <!-- LIST OF COMPANIES -->
        <?php
        $result = $conn->query("SELECT u.*, c.* FROM users u JOIN companies c ON u.user_id=c.user_id ORDER BY c.company_id DESC");
        ?>

        <table class="min-w-full bg-white shadow rounded-xl overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Profile</th>
                    <th class="p-3">Username</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Phone</th>
                    <th class="p-3">Company Name</th>
                    <th class="p-3">Business Type</th>
                    <th class="p-3">Joined</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="p-3">
                            <img src="../uploads/profile/<?= $row['profile_img']; ?>" class="w-12 h-12 rounded-full">
                        </td>
                        <td class="p-3"><?= $row['username']; ?></td>
                        <td class="p-3"><?= $row['user_email']; ?></td>
                        <td class="p-3"><?= $row['user_phone']; ?></td>
                        <td class="p-3"><?= $row['company_name']; ?></td>
                        <td class="p-3"><?= $row['business_type']; ?></td>
                        <td class="p-3 text-gray-600"><?= date("d M Y", strtotime($row['created_at'])); ?></td>
                        <td class="p-3 flex gap-4">
                            <a href="manage_companies.php?action=edit&user_id=<?= $row['user_id']; ?>&company_id=<?= $row['company_id']; ?>"
                                class="text-indigo-600 hover:underline">Edit</a>
                            <a href="manage_companies.php?action=delete&user_id=<?= $row['user_id']; ?>&company_id=<?= $row['company_id']; ?>"
                                onclick="return confirm('Delete company?')" class="text-red-600 hover:underline">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php endif; ?>

</main>

<script src="../assets/jquery.min.js"></script>
<script src="../assets/validate.js?v=<?= time(); ?>"></script>
<?php include 'footer.php'; ?>