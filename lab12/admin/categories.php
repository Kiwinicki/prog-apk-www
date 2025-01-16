<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

/**
 * Adds a new category to the database.
 * @param string $name The name of the category.
 * @param int $parent The id of the parent category (0 for main category).
 */
function addCategory($name, $parent = 0) {
    global $db;
    $name = sanitizeInput($name);
    $parent = (int)$parent;

    $stmt = $db->prepare("INSERT INTO categories (name, parent) VALUES (:name, :parent)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':parent', $parent);
    $stmt->execute();
}

/**
 * Removes a category from the database by its name.
 * @param string $name The name of the category.
 */
function removeCategoryByName($name) {
    global $db;
    $name = sanitizeInput($name);

    $stmt = $db->prepare("DELETE FROM categories WHERE name = :name");
    $stmt->bindParam(':name', $name);
    $stmt->execute();
}

/**
 * Updates category information in the database.
 * @param string $oldName The current name of the category.
 * @param string $newName The new name of the category.
 * @param string $parentName The name of the new parent category.
 */
function updateCategoryByName($oldName, $newName, $parentName) {
    global $db;
    $oldName = sanitizeInput($oldName);
    $newName = sanitizeInput($newName);
    $parentName = sanitizeInput($parentName);

    // Get the parent category ID
    $parentId = 0;
    if (!empty($parentName)) {
        $stmt = $db->prepare("SELECT id FROM categories WHERE name = :name");
        $stmt->bindParam(':name', $parentName);
        $stmt->execute();
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        $parentId = $parent ? $parent['id'] : 0;
    }

    $stmt = $db->prepare("UPDATE categories SET name = :newName, parent = :parent WHERE name = :oldName");
    $stmt->bindParam(':newName', $newName);
    $stmt->bindParam(':parent', $parentId);
    $stmt->bindParam(':oldName', $oldName);
    $stmt->execute();
}

/**
 * Recursive function to display nested categories.
 * @param array $categories Array of all categories.
 * @param int $parent The parent category ID.
 * @param int $level Indentation level.
 */
function displayCategories($categories, $parent = 0, $level = 0) {
    foreach ($categories as $category) {
        if ($category['parent'] == $parent) {
            echo str_repeat("&nbsp;&nbsp;", $level) . "- " . $category['name'] . "<br>";
            displayCategories($categories, $category['id'], $level + 1);
        }
    }
}

// Example usage (can be moved to a separate controller)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $parentName = sanitizeInput($_POST['parent']);
        $parentId = 0;
        if (!empty($parentName)) {
            $stmt = $db->prepare("SELECT id FROM categories WHERE name = :name");
            $stmt->bindParam(':name', $parentName);
            $stmt->execute();
            $parent = $stmt->fetch(PDO::FETCH_ASSOC);
            $parentId = $parent ? $parent['id'] : 0;
        }
        addCategory($_POST['name'], $parentId);
        header("Location: categories.php");
        exit;
    } elseif (isset($_POST['remove_category'])) {
        removeCategoryByName($_POST['category_name']);
        header("Location: categories.php");
        exit;
    } elseif (isset($_POST['update_category'])) {
        updateCategoryByName($_POST['old_name'], $_POST['new_name'], $_POST['parent']);
        header("Location: categories.php");
        exit;
    }
}

$categories = getAllCategories();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Manage Categories</h1>

        <!-- Add Category Form -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Add Category</h2>
            <form action="categories.php" method="post" class="space-y-4">
                <div>
                    <label for="name" class="block text-gray-700 font-bold mb-2">Name</label>
                    <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="parent" class="block text-gray-700 font-bold mb-2">Parent Category</label>
                    <select name="parent" id="parent" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">None (Main Category)</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="add_category" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Category</button>
            </form>
        </section>

        <!-- Remove Category Form -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Remove Category</h2>
            <form action="categories.php" method="post" class="space-y-4">
                <div>
                    <label for="category_name" class="block text-gray-700 font-bold mb-2">Category Name</label>
                    <select name="category_name" id="category_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="remove_category" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Remove Category</button>
            </form>
        </section>

        <!-- Update Category Form -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Update Category</h2>
            <form action="categories.php" method="post" class="space-y-4">
                <div>
                    <label for="old_name" class="block text-gray-700 font-bold mb-2">Current Category Name</label>
                    <select name="old_name" id="old_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="new_name" class="block text-gray-700 font-bold mb-2">New Category Name</label>
                    <input type="text" name="new_name" id="new_name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="parent" class="block text-gray-700 font-bold mb-2">New Parent Category</label>
                    <select name="parent" id="parent" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">None (Main Category)</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['name']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="update_category" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Update Category</button>
            </form>
        </section>

        <!-- Category Tree -->
        <section>
            <h2 class="text-2xl font-semibold mb-2">Category Tree</h2>
            <div class="mb-8">
                <?php displayCategories($categories); ?>
            </div>
        </section>

        <a href="index.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Back to Admin Panel</a>
    </div>
</body>
</html>