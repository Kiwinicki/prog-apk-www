<?php
session_start();
require_once '../utils.php';

/**
 * Gets cart items from session and product information
 * @return array An array of cart items with product data
 */
function getCartItems() {
    global $db;
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    $cart = $_SESSION['cart'];
    $cartItems = [];

    $placeholders = implode(',', array_fill(0, count($cart), '?'));

    $stmt = $db->prepare("SELECT id, title, image, net_price, vat FROM products WHERE id IN ($placeholders)");
    $i = 1;
    foreach (array_keys($cart) as $key) {
        $stmt->bindValue($i, $key);
        $i++;
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($products as $product){
        $cartItems[] = array_merge($product, ['quantity'=>$_SESSION['cart'][$product['id']]['quantity']]);
    }
    return $cartItems;
}

/**
 * Updates quantity of product in the cart
 * @param int $id The ID of the product.
 * @param int $quantity The new quantity of the product.
 */
function updateCartItem($id, $quantity) {
    $id = (int)$id;
    $quantity = (int)$quantity;

    if (isset($_SESSION['cart'][$id])) {
        if($quantity<=0){
            unset($_SESSION['cart'][$id]);
        }else{
            $_SESSION['cart'][$id]['quantity'] = $quantity;
        }
    }

    header("Location: cart.php");
    exit;
}

/**
 * Removes a product from the cart
 * @param int $id The ID of the product to remove.
 */
function removeCartItem($id) {
    $id = (int)$id;

    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }

    header("Location: cart.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        updateCartItem($_POST['product_id'], $_POST['quantity']);
    } elseif (isset($_POST['remove_item'])) {
        removeCartItem($_POST['product_id']);
    }
}

$cartItems = getCartItems();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <p class="text-gray-700">Your cart is empty.</p>
        <?php else: ?>
            <table class="w-full bg-white rounded shadow">
                <thead class="text-left">
                    <tr>
                        <th class="p-4">Product</th>
                        <th class="p-4">Quantity</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Total</th>
                        <th class="p-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td class="p-4 flex items-center">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="w-16 h-16 object-cover mr-2">
                                <?php echo $item['title']; ?>
                            </td>
                            <td class="p-4">
                                <form method="post" action="cart.php" class="flex">
                                    <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" class="shadow appearance-none border rounded w-20 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2" min="0">
                                    <button type="submit" name="update_cart" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                                    <button type="submit" name="remove_item" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2">Remove</button>
                                </form>
                            </td>
                            <td class="p-4">
                                <?php echo $item['net_price'] * (1+ ($item['vat']/100)); ?>
                            </td>
                            <td class="p-4">
                                <?php $itemTotal =  $item['quantity'] * ($item['net_price'] * (1+ ($item['vat']/100)));
                                    $total += $itemTotal;
                                    echo $itemTotal;
                                ?>
                            </td>
                            <td class="p-4"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="font-bold">
                    <tr>
                        <td colspan="3" class="p-4">Total:</td>
                        <td class="p-4"><?php echo $total; ?></td>
                        <td class="p-4"></td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
        <a href="index.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4">Back to Shop</a>
    </div>
</body>
</html>