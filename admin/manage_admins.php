<?php
session_start();
include '../config.php';
include 'header.php';

// ---- ACCESS CHECK ----
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// ACTION HANDLER
$action = $_GET['action'] ?? 'list';

// DELETE ADMIN
if ($action === "delete" && isset($_GET['id'])) {
    $delete_id = $_GET['id'];

    // Prevent deleting own account
    if ($delete_id == $_SESSION['admin_id']) {
        echo "<script>alert('You cannot delete your own admin account.'); window.location='manage_admins.php';</script>";
        exit;
    }

    $conn->query("DELETE FROM admins WHERE admin_id = $delete_id");
    echo "<script>window.location='manage_admins.php';</script>";
    exit;
}

?>

<div class="p-8">

    <?php
    // ------------------------------------
    // 1️⃣ ADD ADMIN FORM
    // ------------------------------------
    if ($action === "add") {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = $_POST['admin_name'];
            $email = $_POST['admin_email'];
            $password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO admins (admin_name, admin_email, admin_password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);
            $stmt->execute();

            echo "<script>window.location='manage_admins.php';</script>";
            exit;
        }
    ?>

        <h2 class="text-2xl font-semibold mb-6">Add Admin</h2>

        <form method="POST" class="bg-white shadow rounded-xl p-6 max-w-lg space-y-5">

            <!-- NAME FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Name</label>
                <input
                    type="text"
                    name="admin_name"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="admin_nameError"></p>
            </div>

            <!-- EMAIL FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input
                    type="email"
                    name="admin_email"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required email">
                <p class="error text-red-500 text-sm mt-1" id="admin_emailError"></p>
            </div>

            <!-- PASSWORD FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Password</label>
                <input
                    type="password"
                    name="admin_password"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required password">
                <p class="error text-red-500 text-sm mt-1" id="admin_passwordError"></p>
            </div>

            <!-- BUTTONS -->
            <div class="flex items-center gap-4 pt-3">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">
                    Add Admin
                </button>
                <a href="manage_admins.php" class="text-indigo-600 hover:underline">
                    Back
                </a>
            </div>

        </form>

    <?php
        // END ADD FORM
    }

    // ------------------------------------
    // 2️⃣ EDIT ADMIN FORM
    // ------------------------------------
    elseif ($action === "edit" && isset($_GET['id'])) {

        $id = $_GET['id'];
        $result = $conn->query("SELECT * FROM admins WHERE admin_id = $id");
        $admin = $result->fetch_assoc();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $name = $_POST['admin_name'];
            $email = $_POST['admin_email'];

            $stmt = $conn->prepare("UPDATE admins SET admin_name=?, admin_email=? WHERE admin_id=?");
            $stmt->bind_param("ssi", $name, $email, $id);
            $stmt->execute();

            echo "<script>window.location='manage_admins.php';</script>";
            exit;
        }
    ?>

        <h2 class="text-2xl font-semibold mb-6">Edit Admin</h2>

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow rounded-xl p-6 max-w-lg space-y-5">

            <!-- NAME FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Name</label>
                <input
                    type="text"
                    name="admin_name"
                    value="<?php echo $admin['admin_name']; ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="admin_nameError"></p>
            </div>

            <!-- EMAIL FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Email</label>
                <input
                    type="email"
                    name="admin_email"
                    value="<?php echo $admin['admin_email']; ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required email">
                <p class="error text-red-500 text-sm mt-1" id="admin_emailError"></p>
            </div>

            <!-- BUTTONS -->
            <div class="flex items-center gap-4 pt-3">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">
                    Save Changes
                </button>

                <a href="manage_admins.php" class="text-indigo-600 hover:underline">
                    Back
                </a>
            </div>

        </form>


    <?php
        // END EDIT
    }

    // ------------------------------------
    // 3️⃣ ADMIN LIST PAGE
    // ------------------------------------
    else {
    ?>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Manage Admins</h2>

            <a href="manage_admins.php?action=add"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-500">
                + Add Admin
            </a>
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 uppercase text-sm">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    <?php
                    $result = $conn->query("SELECT * FROM admins ORDER BY admin_id DESC");

                    while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr>
                            <td class="px-4 py-3 font-medium"><?php echo $row['admin_name']; ?></td>
                            <td class="px-4 py-3"><?php echo $row['admin_email']; ?></td>

                            <td class="px-4 py-3 text-center space-x-4">
                                <a href="manage_admins.php?action=edit&id=<?php echo $row['admin_id']; ?>"
                                    class="text-indigo-600 hover:underline">Edit</a>

                                <a href="manage_admins.php?action=delete&id=<?php echo $row['admin_id']; ?>"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Are you sure you want to delete this admin?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>

            </table>
        </div>

    <?php
        // END LIST
    }
    ?>

    <script src="../assets/jquery.min.js"></script>
    <script src="../assets/validate.js"></script>

</div>
</body>
<?php include 'footer.php'; ?>