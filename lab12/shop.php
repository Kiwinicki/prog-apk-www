<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'cfg.php';
include_once 'helpers.php';

// Establish database connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch all categories
function fetchAllCategories($conn) {
    $query = "SELECT id, nazwa, matka FROM kategorie";
    $result = $conn->query($query);
    if (!$result) return [];
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}
// Function to fetch all products
function fetchAllProducts($conn) {
  $query = "SELECT id, tytul, cena_netto, podatek_vat, kategoria_id FROM produkty";
    $result = $conn->query($query);
    if (!$result) return [];
    $products = [];
    while ($row = $result->fetch_assoc()) {
         $products[] = $row;
    }
    return $products;
}
// Function to add an item to the cart
function addToCard($conn, $id_prod, $ile_sztuk, $wielkosc) {
    if (!isset($_SESSION['count'])) {
        $_SESSION['count'] = 0;
    }
    $_SESSION['count']++;

    $nr = $_SESSION['count']; // Assign a unique number for the cart item
    // Assign values to product object
    $prod[$nr]['id_prod'] = $id_prod;
    $prod[$nr]['ile_sztuk'] = $ile_sztuk;
    $prod[$nr]['wielkosc'] = $wielkosc;
    $prod[$nr]['data'] = time();

    // Mimicking multi-dimensional array for session variable
    $_SESSION[$nr.'_0'] = $nr;
    $_SESSION[$nr.'_1'] = $prod[$nr]['id_prod'];
    $_SESSION[$nr.'_2'] = $prod[$nr]['ile_sztuk'];
    $_SESSION[$nr.'_3'] = $prod[$nr]['wielkosc'];
    $_SESSION[$nr.'_4'] = $prod[$nr]['data'];
}
function updateCartItemQuantity($conn, $cartItemId, $newQuantity) {
  if (isset($_SESSION[$cartItemId . '_2'])) {
     $_SESSION[$cartItemId . '_2'] = $newQuantity;
     return true; // Quantity updated successfully
 }
 return false; // Cart item not found
}
// Function to remove an item from the cart
function removeFromCard($conn, $cartItemId) {
 if (isset($_SESSION[$cartItemId . '_0'])) {
      unset($_SESSION[$cartItemId . '_0']);
     unset($_SESSION[$cartItemId . '_1']);
     unset($_SESSION[$cartItemId . '_2']);
     unset($_SESSION[$cartItemId . '_3']);
     unset($_SESSION[$cartItemId . '_4']);
       // renumber after deleting
       renumberCartItems();
     return true; // Item removed successfully
 }
 return false; // Item not found in the cart
}
function renumberCartItems(){
      if (isset($_SESSION['count'])) {
          $count = $_SESSION['count'];
         $newCount = 0;
         $newCart = [];
         for($i = 1; $i <= $count; $i++){
             if (isset($_SESSION[$i . '_0'])) {
                  $newCount++;
                   $newCart[$newCount .'_0'] = $newCount;
                   $newCart[$newCount .'_1'] = $_SESSION[$i . '_1'];
                   $newCart[$newCount .'_2'] = $_SESSION[$i . '_2'];
                   $newCart[$newCount .'_3'] = $_SESSION[$i . '_3'];
                   $newCart[$newCount .'_4'] = $_SESSION[$i . '_4'];
                }
          }
        // Clear the old cart
        foreach($_SESSION as $key => $value){
           if (str_ends_with($key, '_0') || str_ends_with($key, '_1') ||str_ends_with($key, '_2') ||str_ends_with($key, '_3') ||str_ends_with($key, '_4')) {
                 unset($_SESSION[$key]);
           }
        }
        // Set new count for cart items and re-assign
        $_SESSION['count'] = $newCount;
         foreach ($newCart as $key => $value){
               $_SESSION[$key] = $value;
         }

     }
}
// Function to get all cart items
function showCard($conn) {
 $cartItems = [];
   if (isset($_SESSION['count'])) {
        $count = $_SESSION['count'];
      for($i = 1; $i <= $count; $i++){
            if (isset($_SESSION[$i . '_0'])) {
              $cartItems[] = [
                 'id' => $_SESSION[$i . '_0'],
                 'id_prod' => $_SESSION[$i . '_1'],
                 'ile_sztuk' => $_SESSION[$i . '_2'],
                 'wielkosc' => $_SESSION[$i . '_3'],
                  'data' => $_SESSION[$i . '_4']
              ];
            }

      }
   }
   return $cartItems;
}
// Function to calculate total price of cart
function calculateTotalPrice($conn)
{
$cartItems = showCard($conn);
$totalPrice = 0;
   foreach($cartItems as $item){
           $product = fetchProductById($conn, $item['id_prod']);
             if ($product) {
                  $totalPrice += ($product['cena_netto'] * (1 + $product['podatek_vat']) * $item['ile_sztuk']);
              }

       }
return $totalPrice;
}
// --- Start of Front End ---

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
addToCard($conn, $_GET['id_prod'], $_GET['ile_sztuk'], $_GET['wielkosc']);
header('Location: '.$_SERVER['PHP_SELF']); // Redirect back
exit();
}

// Handle remove from cart
if (isset($_GET['remove_from_cart'])) {
removeFromCard($conn, $_GET['cartItemId']);
  header('Location: '.$_SERVER['PHP_SELF']); // Redirect back
exit();
}

// Handle update quantity in cart
if (isset($_GET['update_quantity'])) {
 updateCartItemQuantity($conn,$_GET['cartItemId'], $_GET['new_quantity']);
header('Location: '.$_SERVER['PHP_SELF']); // Redirect back
exit();
}

$categories = fetchAllCategories($conn);
$products = fetchAllProducts($conn);
$cartItems = showCard($conn);
$totalPrice = calculateTotalPrice($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <title>Shopping Cart</title>
 <style>
      body { font-family: sans-serif; }
     .category { margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; }
     .product { margin-bottom: 10px; padding: 5px; border-bottom: 1px dashed #eee; }
       .cart { margin-top: 30px; border: 1px solid #ddd; padding: 10px; }
 </style>
</head>
<body>

<h1>Product Catalog</h1>
  <?php if (!empty($categories)) : ?>
     <?php foreach ($categories as $category) : ?>
         <div class="category">
             <h2><?php echo sanitize($category['nazwa']); ?></h2>
             <?php
                 foreach ($products as $product) {
                   if ($product['kategoria_id'] == $category['id']){
                         echo '<div class="product">';
                          echo  sanitize($product['tytul']). " - Cena: " . $product['cena_netto'] . " ";
                     echo    '<form method="GET" style="display: inline;">';
                      echo    '<input type="hidden" name="add_to_cart" value="true">';
                       echo   '<input type="hidden" name="id_prod" value="'. $product['id'] .'">';
                       echo   'Ilość: <input type="number" name="ile_sztuk" value="1" min = "1"  style="width:40px" >';
                     echo   '<input type="hidden" name="wielkosc" value="small">'; // Add wielkosc as hidden field
                       echo   '<input type="submit" value="Dodaj do koszyka">';
                         echo   '</form>';
                       echo '</div>';
                   }
                 }
             ?>
         </div>
     <?php endforeach; ?>
  <?php else: ?>
         <p>No categories available.</p>
     <?php endif; ?>


 <div class="cart">
     <h2>Your Cart</h2>
       <?php if (!empty($cartItems)) : ?>
         <ul>
           <?php foreach ($cartItems as $item) :?>
             <li>
              ID: <?php echo  $item['id']?> | ID Produktu: <?php echo  $item['id_prod']?> | Ilość sztuk: <?php echo  $item['ile_sztuk']?> | Wielkość: <?php echo  $item['wielkosc']?> |
               <a href='?remove_from_cart=true&cartItemId=<?php echo  $item['id']?>'>Usuń</a>
                 <form method = 'GET' style="display: inline;">
                   <input type='hidden' name='update_quantity' value ='true'/>
                    <input type='hidden' name='cartItemId' value ='<?php echo  $item['id']?>'/>
                  Ilość: <input type='number' name='new_quantity' value='<?php echo  $item['ile_sztuk']?>' min='1'  style="width:40px"/>
                   <input type='submit' value='Zmień'/>
                    </form>
             </li>
           <?php endforeach; ?>
         </ul>
        <p>Total Price: <?php echo $totalPrice; ?></p>
     <?php else : ?>
         <p>Your cart is empty.</p>
     <?php endif; ?>
 </div>
</body>
</html>

<?php
$conn->close();
?>