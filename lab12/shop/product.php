<?php
session_start();
require_once '../utils.php';


if (isset($_GET['id'])) {
    $product = getProductById($_GET['id']);
} else {
    header('Location: index.php');
    exit;
}

if (!$product) {
    echo "Product not found.";
    exit;
}

/**
 * Adds an item to the cart using $_SESSION
 * @param int $id The ID of the product.
 * @param int $quantity The quantity of the product.
 */
function addToCart($id, $quantity) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $id = (int)$id;
     $quantity = (int)$quantity;

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$id] = ['quantity' => $quantity];
    }

     header("Location: product.php?id=".$id);
     exit;

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
  addToCart($_GET['id'], $_POST['quantity']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4"><?php echo $product['title']; ?></h1>
         <div class="bg-white p-4 rounded shadow flex justify-center">
            <div class="w-1/2">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="w-full h-auto object-cover mb-4">
             </div>
            <div class="w-1/2">
                  <p class="text-gray-700 mb-4"><?php echo $product['description']; ?></p>
                  <p class="text-gray-700 mb-4">Price: <?php echo $product['net_price'] * (1+ ($product['vat']/100)); ?> </p>
                <form action="product.php?id=<?php echo $product['id']; ?>" method="post" class="space-y-4">
                    <div>
                         <label for="quantity" class="block text-gray-700 font-bold mb-2">Quantity</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                     <button type="submit" name="add_to_cart" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add to Cart</button>
                 </form>
            </div>
        </div>
        <a href="index.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4">Back to Products</a>
    </div>
</body>
</html>