<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    redirectToLogin();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        addProduct(
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
    } elseif (isset($_POST['update_product'])) {
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
    } elseif (isset($_POST['remove_product'])) {
        removeProduct((int)$_POST['product_id']);
        header("Location: products.php");
        exit;
    }
}

$products = getAllProducts();
$categories = getAllCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Manage Products</h1>

        <!-- Add Product Form -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Add Product</h2>
            <form action="products.php" method="post" class="space-y-4">
                <div>
                    <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                    <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                    <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
                </div>
                <div>
                    <label for="net_price" class="block text-gray-700 font-bold mb-2">Net Price</label>
                    <input type="number" step="0.01" name="net_price" id="net_price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="vat" class="block text-gray-700 font-bold mb-2">VAT (%)</label>
                    <input type="number" step="0.01" name="vat" id="vat" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="stock_quantity" class="block text-gray-700 font-bold mb-2">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div>
                    <label for="status" class="block text-gray-700 font-bold mb-2">Status</label>
                    <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div>
                    <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                    <select name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="dimensions" class="block text-gray-700 font-bold mb-2">Dimensions</label>
                    <input type="text" name="dimensions" id="dimensions" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div>
                    <label for="image" class="block text-gray-700 font-bold mb-2">Image URL</label>
                    <input type="text" name="image" id="image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" name="add_product" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Product</button>
            </form>
        </section>

        <!-- Product List -->
        <section>
            <h2 class="text-2xl font-semibold mb-2">Product List</h2>
            <table class="w-full bg-white rounded shadow">
                <thead>
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Title</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Stock</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td class="p-4"><?php echo $product['id']; ?></td>
                            <td class="p-4"><?php echo $product['title']; ?></td>
                            <td class="p-4"><?php echo $product['category']; ?></td>
                            <td class="p-4"><?php echo $product['net_price']; ?></td>
                            <td class="p-4"><?php echo $product['stock_quantity']; ?></td>
                            <td class="p-4">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">Edit</a>
                                <form action="products.php" method="post" class="inline">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="remove_product" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Remove</button>
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