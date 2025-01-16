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

$products = getAllProducts();
$cartItems = getCartItems();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel= "stylesheet" href= "https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css" >
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
           <h1 class="text-3xl font-bold">Welcome to our shop</h1>
              <div class="cart-icon relative">
                <i class="las la-shopping-cart bg-blue-500 text-5xl rounded p-1 text-white"></i>
                 <div class="cart-popup">
                    <?php if (!empty($cartItems)): ?>
                       <ul>
                           <?php foreach ($cartItems as $item): ?>
                             <li>
                               <?php echo $item['title']; ?> (<?php echo $item['quantity']; ?>)
                             </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="cart.php" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded mt-2">Go to Cart</a>
                    <?php else: ?>
                      <p>Cart is empty</p>
                   <?php endif; ?>
                 </div>

              </div>

         </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($products as $product): ?>
           <div class="bg-white p-4 rounded shadow">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="w-full h-48 object-cover mb-2">
                <h2 class="text-xl font-semibold mb-2"><?php echo $product['title']; ?></h2>
                <p class="text-gray-700 mb-2"><?php echo substr($product['description'],0,100); ?>...</p>
                <a href="product.php?id=<?php echo $product['id']; ?>" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">View Details</a>
            </div>
        <?php endforeach; ?>
       </div>
    </div>
    <footer class="bg-gray-800 text-gray-300 py-4 mt-8">
        <div class="mx-auto text-center">
            <a href="../index.php">Back to AI page</a>
        </div>
    </footer>
</body>
</html>