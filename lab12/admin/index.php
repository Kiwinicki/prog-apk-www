<?php
session_start();
require_once '../utils.php';

if (!isLoggedIn()) {
    redirectToLogin();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Admin Panel</h1>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Categories</h2>
            <p>Manage product categories.</p>
            <a href="categories.php" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Categories</a>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Products</h2>
            <p>Manage products.</p>
           <a href="products.php" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Products</a>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-2">Pages</h2>
            <p>Manage pages.</p>
            <a href="pages.php" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Go to Pages</a>
        </section>

        <p class="mb-8">
            <a href="../index.php">Go back to pages</a>
        </p>
        <p class="mb-8">
            <a href="../shop/index.php">Go back to shop</a>
        </p>
    </div>
</body>
</html>