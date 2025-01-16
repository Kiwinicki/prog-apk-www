<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product = getProductById($_GET['id']);
if (!$product) {
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    updateProduct(
        (int)$_POST['product_id'],
        sanitizeInput($_POST['title']),
        sanitizeInput($_POST['description']),
        (float)$_POST['net_price'],
        (float)$_POST['vat'],
        (int)$_POST['stock_quantity'],
        (int)$_POST['status'],
        (int)$_POST['category'],
        sanitizeInput($_POST['dimensions']),
        sanitizeInput($_POST['image'])
    );
    header("Location: products.php");
    exit;
}

$categories = getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Edit Product</h1>
        <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="post" class="space-y-4">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div>
                <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                <input type="text" name="title" id="title" value="<?php echo $product['title']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required><?php echo $product['description']; ?></textarea>
            </div>
            <div>
                <label for="net_price" class="block text-gray-700 font-bold mb-2">Net Price</label>
                <input type="number" step="0.01" name="net_price" id="net_price" value="<?php echo $product['net_price']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="vat" class="block text-gray-700 font-bold mb-2">VAT (%)</label>
                <input type="number" step="0.01" name="vat" id="vat" value="<?php echo $product['vat']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="stock_quantity" class="block text-gray-700 font-bold mb-2">Stock Quantity</label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div>
                <label for="status" class="block text-gray-700 font-bold mb-2">Status</label>
                <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="1" <?php echo $product['status'] == 1 ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo $product['status'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div>
                <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                <select name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo $product['category'] == $category['id'] ? 'selected' : ''; ?>><?php echo $category['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="dimensions" class="block text-gray-700 font-bold mb-2">Dimensions</label>
                <input type="text" name="dimensions" id="dimensions" value="<?php echo $product['dimensions']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="image" class="block text-gray-700 font-bold mb-2">Image URL</label>
                <input type="text" name="image" id="image" value="<?php echo $product['image']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <button type="submit" name="update_product" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Product</button>
        </form>
        <a href="products.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4">Back to Products</a>
    </div>
</body>
</html>