<?php
session_start();
include '../config.php';
include 'header.php';

// ---- ACCESS CHECK ----
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// action handler
$action = $_GET['action'] ?? 'list';

// helper: upload profile image
function upload_profile_image($file, $old_filename = null)
{
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $old_filename; // keep old if exists
    }

    // basic checks
    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Invalid image type. Allowed: JPG, PNG.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) { // 2MB
        return ['error' => 'Image size must be less than 2MB.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetDir = __DIR__ . '/../uploads/profile/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $target = $targetDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['error' => 'Failed to move uploaded file.'];
    }

    // remove old file if provided and exists
    if ($old_filename) {
        $oldPath = $targetDir . $old_filename;
        if (file_exists($oldPath) && is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $filename;
}

// ------------------ DELETE (remove both company + user) ------------------
if ($action === 'delete' && isset($_GET['user_id']) && isset($_GET['company_id'])) {
    $user_id = (int)$_GET['user_id'];
    $company_id = (int)$_GET['company_id'];

    // get profile image to delete file
    $stmt = $conn->prepare("SELECT profile_img FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $profile_img = $res['profile_img'] ?? null;
    $stmt->close();

    // delete company row
    $stmt = $conn->prepare("DELETE FROM companies WHERE company_id = ?");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $stmt->close();

    // delete user row
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // delete profile file if exists
    if ($profile_img) {
        $p = __DIR__ . '/../uploads/profile/' . $profile_img;
        if (file_exists($p)) @unlink($p);
    }

    header("Location: manage_companies.php");
    exit;
}

?>

<div class="p-8">

    <?php
    // ------------------ ADD (user + company) ------------------
    if ($action === 'add') {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // user fields
            $username = trim($_POST['username']);
            $user_email = trim($_POST['user_email']);
            $role = 'company';
            $user_phone = trim($_POST['user_phone']);
            $user_password = $_POST['user_password'];

            // company fields
            $company_name = trim($_POST['company_name']);
            $company_address = trim($_POST['company_address']);
            $company_description = trim($_POST['company_description']);
            $company_website = trim($_POST['company_website']);
            $business_type = trim($_POST['business_type']);

            // handle profile image
            $uploaded = upload_profile_image($_FILES['profile_img'] ?? null, null);
            if (is_array($uploaded) && isset($uploaded['error'])) {
                $upload_error = $uploaded['error'];
            } else {
                $profile_img = $uploaded;
            }

            if (empty($upload_error)) {
                // create user
                $password_hash = password_hash($user_password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO users (username, user_email, role, user_phone, user_password, profile_img, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssss", $username, $user_email, $role, $user_phone, $password_hash, $profile_img);
                $stmt->execute();
                $new_user_id = $conn->insert_id;
                $stmt->close();

                // create company record
                $stmt = $conn->prepare("INSERT INTO companies (user_id, company_name, company_address, company_description, company_website, business_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("isssss", $new_user_id, $company_name, $company_address, $company_description, $company_website, $business_type);
                $stmt->execute();
                $stmt->close();

                header("Location: manage_companies.php");
                exit;
            }
        }
    ?>

        <h2 class="text-2xl font-semibold mb-6">Add Company (User + Company)</h2>

        <?php if (!empty($upload_error)): ?>
            <div class="mb-4 text-red-600"><?php echo htmlspecialchars($upload_error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-xl p-6 max-w-lg space-y-5">

            <!-- USERNAME -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Username</label>
                <input type="text" name="username" class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="usernameError"></p>
            </div>

            <!-- EMAIL -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" name="user_email" class="w-full border px-3 py-2 rounded" data-validation="required email">
                <p class="error text-red-500 text-sm mt-1" id="user_emailError"></p>
            </div>

            <!-- PHONE -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Phone</label>
                <input type="text" name="user_phone" class="w-full border px-3 py-2 rounded" data-validation="numeric">
                <p class="error text-red-500 text-sm mt-1" id="user_phoneError"></p>
            </div>

            <!-- PASSWORD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input type="password" name="user_password" class="w-full border px-3 py-2 rounded" data-validation="required strongPassword">
                <p class="error text-red-500 text-sm mt-1" id="user_passwordError"></p>
            </div>

            <!-- PROFILE IMG -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Profile Image</label>
                <input type="file" name="profile_img" accept="image/*" class="w-full" data-validation="file">
                <p class="error text-red-500 text-sm mt-1" id="profile_imgError"></p>
            </div>

            <hr class="my-4">

            <!-- COMPANY NAME -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Company Name</label>
                <input type="text" name="company_name" class="w-full border px-3 py-2 rounded" data-validation="required">
                <p class="error text-red-500 text-sm mt-1" id="company_nameError"></p>
            </div>

            <!-- COMPANY ADDRESS -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Company Address</label>
                <textarea name="company_address" class="w-full border px-3 py-2 rounded" data-validation="required"></textarea>
                <p class="error text-red-500 text-sm mt-1" id="company_addressError"></p>
            </div>

            <!-- DESCRIPTION -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea name="company_description" class="w-full border px-3 py-2 rounded" data-validation="required"></textarea>
                <p class="error text-red-500 text-sm mt-1" id="company_descriptionError"></p>
            </div>

            <!-- WEBSITE -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Website</label>
                <input type="text" name="company_website" class="w-full border px-3 py-2 rounded" data-validation="url">
                <p class="error text-red-500 text-sm mt-1" id="company_websiteError"></p>
            </div>

            <!-- BUSINESS TYPE -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Business Type</label>
                <input type="text" name="business_type" class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="business_typeError"></p>
            </div>

            <div class="flex items-center gap-4 pt-3">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">Add Company</button>
                <a href="manage_companies.php" class="text-indigo-600 hover:underline">Back</a>
            </div>
        </form>

        <?php
        // end add
    }

    // ------------------ EDIT (update both user + company) ------------------
    elseif ($action === 'edit' && isset($_GET['user_id']) && isset($_GET['company_id'])) {

        $user_id = (int)$_GET['user_id'];
        $company_id = (int)$_GET['company_id'];

        // fetch joined data
        $stmt = $conn->prepare("
        SELECT u.user_id, u.username, u.user_email, u.role, u.user_phone, u.profile_img,
               c.company_id, c.company_name, c.company_address, c.company_description, c.company_website, c.business_type
        FROM users u
        JOIN companies c ON u.user_id = c.user_id
        WHERE u.user_id = ? AND c.company_id = ?
        LIMIT 1
    ");
        $stmt->bind_param("ii", $user_id, $company_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) {
            echo "<div class='text-red-600'>Company/User not found.</div>";
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // user fields
                $username = trim($_POST['username']);
                $user_email = trim($_POST['user_email']);
                $user_phone = trim($_POST['user_phone']);
                $password = $_POST['user_password']; // may be empty -> don't change
                // company fields
                $company_name = trim($_POST['company_name']);
                $company_address = trim($_POST['company_address']);
                $company_description = trim($_POST['company_description']);
                $company_website = trim($_POST['company_website']);
                $business_type = trim($_POST['business_type']);

                // handle profile upload (keep old if no new)
                $uploaded = upload_profile_image($_FILES['profile_img'] ?? null, $row['profile_img']);
                if (is_array($uploaded) && isset($uploaded['error'])) {
                    $upload_error = $uploaded['error'];
                } else {
                    $profile_img = $uploaded;
                }

                if (empty($upload_error)) {
                    // update users table
                    if (!empty($password)) {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET username=?, user_email=?, user_phone=?, user_password=?, profile_img=? WHERE user_id=?");
                        $stmt->bind_param("sssssi", $username, $user_email, $user_phone, $password_hash, $profile_img, $user_id);
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET username=?, user_email=?, user_phone=?, profile_img=? WHERE user_id=?");
                        $stmt->bind_param("ssssi", $username, $user_email, $user_phone, $profile_img, $user_id);
                    }
                    $stmt->execute();
                    $stmt->close();

                    // update companies table
                    $stmt = $conn->prepare("UPDATE companies SET company_name=?, company_address=?, company_description=?, company_website=?, business_type=? WHERE company_id=?");
                    $stmt->bind_param("sssssi", $company_name, $company_address, $company_description, $company_website, $business_type, $company_id);
                    $stmt->execute();
                    $stmt->close();

                    header("Location: manage_companies.php");
                    exit;
                }
            }
        ?>

            <h2 class="text-2xl font-semibold mb-6">Edit Company & User</h2>

            <?php if (!empty($upload_error)): ?>
                <div class="mb-4 text-red-600"><?php echo htmlspecialchars($upload_error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-xl p-6 max-w-lg space-y-5">

                <!-- USERNAME -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                    <p class="error text-red-500 text-sm mt-1" id="usernameError"></p>
                </div>

                <!-- EMAIL -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" name="user_email" value="<?php echo htmlspecialchars($row['user_email']); ?>" class="w-full border px-3 py-2 rounded" data-validation="required email">
                    <p class="error text-red-500 text-sm mt-1" id="user_emailError"></p>
                </div>

                <!-- PHONE -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Phone</label>
                    <input type="text" name="user_phone" value="<?php echo htmlspecialchars($row['user_phone']); ?>" class="w-full border px-3 py-2 rounded" data-validation="numeric">
                    <p class="error text-red-500 text-sm mt-1" id="user_phoneError"></p>
                </div>

                <!-- PASSWORD (leave blank to keep current) -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Password (leave blank to keep unchanged)</label>
                    <input type="password" name="user_password" class="w-full border px-3 py-2 rounded" data-validation="password">
                    <p class="error text-red-500 text-sm mt-1" id="user_passwordError"></p>
                </div>

                <!-- PROFILE IMG -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Profile Image</label>
                    <?php if (!empty($row['profile_img'])): ?>
                        <div class="mb-2">
                            <img src="../uploads/profile/<?php echo htmlspecialchars($row['profile_img']); ?>" class="w-20 h-20 rounded-full object-cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="profile_img" accept="image/*" class="w-full" data-validation="file">
                    <p class="error text-red-500 text-sm mt-1" id="profile_imgError"></p>
                </div>

                <hr class="my-4">

                <!-- COMPANY NAME -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Company Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($row['company_name']); ?>" class="w-full border px-3 py-2 rounded" data-validation="required">
                    <p class="error text-red-500 text-sm mt-1" id="company_nameError"></p>
                </div>

                <!-- COMPANY ADDRESS -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Company Address</label>
                    <textarea name="company_address" class="w-full border px-3 py-2 rounded" data-validation="required"><?php echo htmlspecialchars($row['company_address']); ?></textarea>
                    <p class="error text-red-500 text-sm mt-1" id="company_addressError"></p>
                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Description</label>
                    <textarea name="company_description" class="w-full border px-3 py-2 rounded" data-validation="required"><?php echo htmlspecialchars($row['company_description']); ?></textarea>
                    <p class="error text-red-500 text-sm mt-1" id="company_descriptionError"></p>
                </div>

                <!-- WEBSITE -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Website</label>
                    <input type="text" name="company_website" value="<?php echo htmlspecialchars($row['company_website']); ?>" class="w-full border px-3 py-2 rounded" data-validation="required">
                    <p class="error text-red-500 text-sm mt-1" id="company_websiteError"></p>
                </div>

                <!-- BUSINESS TYPE -->
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Business Type</label>
                    <input type="text" name="business_type" value="<?php echo htmlspecialchars($row['business_type']); ?>" class="w-full border px-3 py-2 rounded" data-validation="required alpha">
                    <p class="error text-red-500 text-sm mt-1" id="business_typeError"></p>
                </div>

                <div class="flex items-center gap-4 pt-3">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">Save Changes</button>
                    <a href="manage_companies.php" class="text-indigo-600 hover:underline">Back</a>
                </div>
            </form>

        <?php
        } // end row found
    }

    // ------------------ LIST (JOIN users + companies) ------------------
    else {
        // fetch joined records
        $query = "
        SELECT 
        u.user_id, 
        u.username, 
        u.user_email, 
        u.user_phone, 
        u.profile_img, 
        u.created_at AS user_created,
        c.company_id,
        c.company_name,
        c.company_address,
        c.business_type,
        c.created_at AS company_created
    FROM users u
    JOIN companies c ON u.user_id = c.user_id
    ORDER BY c.company_id DESC
    ";
        $result = $conn->query($query);
        ?>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Manage Companies</h2>
            <a href="manage_companies.php?action=add" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-500">+ Add Company</a>
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <h2 class="text-2xl font-semibold mb-6">Companies List</h2>

            <table class="min-w-full bg-white shadow rounded-xl overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Image</th>
                        <th class="p-3 text-left">Username</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Phone</th>
                        <th class="p-3 text-left">Company Name</th>
                        <th class="p-3 text-left">Business Type</th>
                        <th class="p-3 text-left">Created At</th>
                        <th class="p-3 text-left">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="p-3">
                                <img src="../uploads/profile/<?php echo $row['profile_img']; ?>" class="w-12 h-12 rounded-full object-cover">
                            </td>

                            <td class="p-3"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row['user_phone']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row['company_name']); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row['business_type']); ?></td>

                            <!-- DATE FIXED HERE -->
                            <td class="p-3 text-gray-600">
                                <?php echo date("d M Y", strtotime($row['user_created'])); ?>
                            </td>

                            <td class="p-3 flex gap-3">
                                <a href="manage_companies.php?action=edit&user_id=<?php echo $row['user_id']; ?>&company_id=<?php echo $row['company_id']; ?>" class="text-indigo-600 hover:underline">
                                    Edit
                                </a>
                                <a href="manage_companies.php?action=delete&user_id=<?php echo $row['user_id']; ?>&company_id=<?php echo $row['company_id']; ?>"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Delete this company?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>

    <?php
    } // end else list
    ?>

    <!-- include validate scripts same as manage_admins -->
    <script src="../assets/jquery.min.js"></script>
    <script src="../assets/validate.js"></script>

    <?php include 'footer.php'; ?>
</div>
</body>