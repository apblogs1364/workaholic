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

// DELETE CATEGORY
if ($action === "delete" && isset($_GET['id'])) {

    $delete_id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();

    echo "<script>window.location='manage_categories.php';</script>";
    exit;
}
?>

<div class="p-8">

    <?php
    // ------------------------------------
    // 1️⃣ ADD CATEGORY FORM
    // ------------------------------------
    if ($action === "add") {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $name = trim($_POST['category_name']);
            $desc = trim($_POST['category_description']);

            $stmt = $conn->prepare("INSERT INTO categories (category_name, category_description) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $desc);
            $stmt->execute();

            echo "<script>window.location='manage_categories.php';</script>";
            exit;
        }
    ?>

        <h2 class="text-2xl font-semibold mb-6">Add Category</h2>

        <form method="POST" class="bg-white shadow rounded-xl p-6 max-w-lg space-y-5 validateForm">

            <!-- NAME FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Category Name</label>
                <input
                    type="text"
                    name="category_name"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="category_nameError"></p>
            </div>

            <!-- DESCRIPTION FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea
                    name="category_description"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required"></textarea>
                <p class="error text-red-500 text-sm mt-1" id="category_descriptionError"></p>
            </div>

            <!-- BUTTONS -->
            <div class="flex items-center gap-4 pt-3">
                <button type="submit" name="add_category"
                    class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">
                    Add Category
                </button>

                <a href="manage_categories.php" class="text-indigo-600 hover:underline">
                    Back
                </a>
            </div>

        </form>

    <?php
        // END ADD
    }

    // ------------------------------------
    // 2️⃣ EDIT CATEGORY FORM
    // ------------------------------------
    elseif ($action === "edit" && isset($_GET['id'])) {

        $id = intval($_GET['id']);

        $res = $conn->query("SELECT * FROM categories WHERE category_id = $id");
        $cat = $res->fetch_assoc();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $name = trim($_POST['category_name']);
            $desc = trim($_POST['category_description']);

            $stmt = $conn->prepare("UPDATE categories SET category_name=?, category_description=? WHERE category_id=?");
            $stmt->bind_param("ssi", $name, $desc, $id);
            $stmt->execute();

            echo "<script>window.location='manage_categories.php';</script>";
            exit;
        }
    ?>

        <h2 class="text-2xl font-semibold mb-6">Edit Category</h2>

        <form method="POST" class="bg-white shadow rounded-xl p-6 max-w-lg space-y-5 validateForm">

            <!-- NAME FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Category Name</label>
                <input
                    type="text"
                    name="category_name"
                    value="<?php echo htmlspecialchars($cat['category_name']); ?>"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required alpha">
                <p class="error text-red-500 text-sm mt-1" id="category_nameError"></p>
            </div>

            <!-- DESCRIPTION FIELD -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea
                    name="category_description"
                    class="w-full border px-3 py-2 rounded"
                    data-validation="required"><?php echo htmlspecialchars($cat['category_description']); ?></textarea>
                <p class="error text-red-500 text-sm mt-1" id="category_descriptionError"></p>
            </div>

            <!-- BUTTONS -->
            <div class="flex items-center gap-4 pt-3">
                <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-500">
                    Save Changes
                </button>

                <a href="manage_categories.php" class="text-indigo-600 hover:underline">
                    Back
                </a>
            </div>

        </form>

    <?php
        // END EDIT
    }

    // ------------------------------------
    // 3️⃣ LIST PAGE
    // ------------------------------------
    else {
    ?>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Manage Categories</h2>

            <a href="manage_categories.php?action=add"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-500">
                + Add Category
            </a>
        </div>

        <div class="bg-white shadow rounded-xl p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 uppercase text-sm">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    <?php
                    $result = $conn->query("SELECT * FROM categories ORDER BY category_id DESC");

                    while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td class="px-4 py-3 font-medium">
                                <?php echo htmlspecialchars($row['category_name']); ?>
                            </td>

                            <td class="px-4 py-3">
                                <?php echo htmlspecialchars($row['category_description']); ?>
                            </td>

                            <td class="px-4 py-3 text-center space-x-4">

                                <a href="manage_categories.php?action=edit&id=<?php echo $row['category_id']; ?>"
                                    class="text-indigo-600 hover:underline">
                                    Edit
                                </a>

                                <a href="manage_categories.php?action=delete&id=<?php echo $row['category_id']; ?>"
                                    class="text-red-600 hover:underline"
                                    onclick="return confirm('Are you sure you want to delete this category?');">
                                    Delete
                                </a>

                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        </div>

    <?php } ?>

    <script src="../assets/jquery.min.js"></script>
    <script src="../assets/validate.js"></script>

</div>
</body>
<?php include 'footer.php'; ?>