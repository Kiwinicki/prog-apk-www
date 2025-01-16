<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

// Database connection
global $db;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_page'])) {
            $title = sanitizeInput($_POST['title']);
            $content = sanitizeInput($_POST['content']);
            $alias = sanitizeInput($_POST['alias']);
            $status = isset($_POST['status']) ? 1 : 0;
            addPage($title, $content, $alias, $status);
            header("Location: pages.php");
            exit;
        } elseif (isset($_POST['update_page'])) {
            $id = (int)$_POST['id'];
            $title = sanitizeInput($_POST['title']);
            $content = sanitizeInput($_POST['content']);
            $alias = sanitizeInput($_POST['alias']);
            $status = isset($_POST['status']) ? 1 : 0;
            updatePage($id, $title, $content, $alias, $status);
            header("Location: pages.php");
            exit;
        } elseif (isset($_POST['delete_page'])) {
            $id = (int)$_POST['id'];
            deletePage($id);
            header("Location: pages.php");
            exit;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch all pages
$pages = getAllPages();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Manage Pages</h1>

        <!-- Add Page Form -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Add New Page</h2>
            <form action="pages.php" method="post" class="space-y-4">
                <div>
                    <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                    <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="alias" class="block text-gray-700 font-bold mb-2">Alias (URL Slug, max 40 chars)</label>
                    <input type="text" name="alias" id="alias" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required maxlength="40">
                </div>
                <div>
                    <label for="content" class="block text-gray-700 font-bold mb-2">Content</label>
                    <textarea name="content" id="content" rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                </div>
                <div>
                    <input type="checkbox" name="status" id="status" value="1" checked>
                    <label for="status" class="text-gray-700 font-bold">Active</label>
                </div>
                <button type="submit" name="add_page" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Page</button>
            </form>
        </section>

        <!-- Page List -->
        <section>
            <h2 class="text-2xl font-semibold mb-2">Page List</h2>
            <table class="w-full bg-white rounded shadow">
                <thead>
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Title</th>
                        <th class="p-4">Alias</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td class="p-4"><?php echo $page['id']; ?></td>
                            <td class="p-4"><?php echo $page['page_title']; ?></td>
                            <td class="p-4"><?php echo $page['alias']; ?></td>
                            <td class="p-4"><?php echo $page['status'] ? 'Active' : 'Inactive'; ?></td>
                            <td class="p-4">
                                <a href="edit_page.php?id=<?php echo $page['id']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</a>
                                <form action="pages.php" method="post" class="inline">
                                    <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
                                    <button type="submit" name="delete_page" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <a href="index.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4">Back to Admin Panel</a>
    </div>
</body>
</html>