<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

if (!isset($_GET['id'])) {
    header('Location: pages.php');
    exit;
}

$page = getPageById($_GET['id']);
if (!$page) {
    header('Location: pages.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_page'])) {
    $id = (int)$_POST['id'];
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    $alias = sanitizeInput($_POST['alias']);
    $status = isset($_POST['status']) ? 1 : 0;
    updatePage($id, $title, $content, $alias, $status);
    header("Location: pages.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Edit Page</h1>
        <form action="edit_page.php?id=<?php echo $page['id']; ?>" method="post" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo $page['id']; ?>">
            <div>
                <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                <input type="text" name="title" id="title" value="<?php echo $page['page_title']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="alias" class="block text-gray-700 font-bold mb-2">Alias (URL Slug)</label>
                <input type="text" name="alias" id="alias" value="<?php echo $page['alias']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="content" class="block text-gray-700 font-bold mb-2">Content</label>
                <textarea name="content" id="content" rows="10" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo $page['page_content']; ?></textarea>
            </div>
            <div>
                <input type="checkbox" name="status" id="status" value="1" <?php echo $page['status'] ? 'checked' : ''; ?>>
                <label for="status" class="text-gray-700 font-bold">Active</label>
            </div>
            <button type="submit" name="update_page" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Page</button>
        </form>
        <a href="pages.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4">Back to Pages</a>
    </div>
</body>
</html>