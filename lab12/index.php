<?php
// Enable error reporting for debugging (remove for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'cfg.php';
include_once 'mail.php';

// Establish database connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user input
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''));
}

// Function to format currency
function formatPrice($price) {
    return number_format($price, 2, ',', ' ');
}

// Function to fetch a page based on alias or ID
function fetchPage($conn, $alias) {
    $stmt = $conn->prepare("SELECT * FROM page_list WHERE alias = ? OR (alias IS NULL AND id = ?) LIMIT 1");
    $stmt->bind_param("si", $alias, $alias);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false || $result->num_rows === 0) {
      return null;
    }
    $page = $result->fetch_assoc();
    $stmt->close();
    return $page;
}

// Function to generate the front-end category tree
function generateCategoryTreeFront($conn, $parent = 0, $level = 0) {
    $stmt = $conn->prepare("SELECT * FROM kategorie WHERE matka = ?");
    $stmt->bind_param("i", $parent);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        return "Database error: " . $conn->error;
    }
    $wynik = "<ul>";
    while ($row = $result->fetch_assoc()) {
        $wynik .= "<li>" . str_repeat("   ", $level) . sanitize($row['nazwa']);
        $wynik .= generateCategoryTreeFront($conn, $row['id'], $level + 1);
        $wynik .= "</li>";
    }
    $wynik .= "</ul>";
    $stmt->close();
    return $wynik;
}

// Function to display categories on front-end
function displayCategoriesFront($conn) {
    $categoryTree = generateCategoryTreeFront($conn);
    return "<h2>Kategorie</h2>" . $categoryTree;
}

// Function to fetch products with category names
function fetchProductsFront($conn){
      $query = "SELECT p.*, k.nazwa as kategoria_nazwa FROM produkty p LEFT JOIN kategorie k ON p.kategoria_id = k.id LIMIT 100";
    $result = $conn->query($query);
    if (!$result) {
        return null; // Return null if the query fails
    }
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Function to display products on front-end
function displayProductsFront($conn) {
    $products = fetchProductsFront($conn);

    if (!$products) {
        return "<p>Brak produktów do wyświetlenia.</p>";
    }

    $wynik = "<h2>Produkty</h2><ul style='list-style-type: none; padding:0; margin:0'>";
    foreach ($products as $row) {
        $wynik .= "<li style='padding:0.5rem; margin:0.5rem; border:1px solid #ccc; border-radius:4px;'>
            <h3 style='margin-bottom: 0.5rem; text-align: left;'>" . sanitize($row['tytul']) . "</h3>
            Cena: " . formatPrice($row['cena_netto']) . " zł
              <form method='post' style='display: inline-block; margin-left:1rem;'>
            <input type='hidden' name='id_prod' value='" . $row['id'] . "'>
            <input type='hidden' name='wielkosc' value='S'>
            <input type='number' name='ile_sztuk' value='1' min='1' style='width: 50px;'>
            <button type='submit' name='add_to_cart' class='submit-button' style='padding: 0.2rem 0.5rem; font-size: 0.9rem;'>Dodaj do koszyka</button>
        </form>
            </li>";
    }
    $wynik .= "</ul>";
    return $wynik;
}

// Function to add product to cart
function addToCart() {
    if (isset($_POST['add_to_cart'])) {
        if (!isset($_SESSION['cart_count'])) {
            $_SESSION['cart_count'] = 1;
        } else {
            $_SESSION['cart_count']++;
        }
        $nr = $_SESSION['cart_count'];
        $id_prod = sanitize($_POST['id_prod']);
        $ile_sztuk = intval($_POST['ile_sztuk']);
        $wielkosc = sanitize($_POST['wielkosc']);
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id_prod);
        $stmt->execute();
        $result = $stmt->get_result();
        if(!$result){
           return false;
        }
        $product = $result->fetch_assoc();
          $stmt->close();
           if($product){
               $prod[$nr] ['id_prod'] = $id_prod;
               $prod[$nr] ['ile_sztuk'] = $ile_sztuk;
               $prod[$nr] ['wielkosc'] = $wielkosc;
               $prod[$nr] ['data'] = time();
               $prod[$nr] ['price'] = $product['cena_netto'] + ($product['cena_netto'] * $product['podatek_vat']);

               $nr_0=$nr.'_0';
               $nr_1=$nr.'_1';
               $nr_2=$nr.'_2';
               $nr_3=$nr.'_3';
               $nr_4=$nr.'_4';
               $nr_5=$nr.'_5';

               $_SESSION[$nr_0] = $nr;
               $_SESSION[$nr_1] = $prod[$nr]['id_prod'];
               $_SESSION[$nr_2] = $prod[$nr] ['ile_sztuk'];
               $_SESSION[$nr_3] = $prod[$nr] ['wielkosc'];
               $_SESSION[$nr_4] = $prod[$nr] ['data'];
               $_SESSION[$nr_5] = $prod[$nr] ['price'];

               return true;
           }
        }
    return false;
}

// Function to show cart items
function showCart(){
    $cart_items = [];
    $total_price = 0;
    if(isset($_SESSION['cart_count'])){
        for($i=1;$i<=$_SESSION['cart_count']; $i++){
            $nr_0=$i.'_0';
            $nr_1=$i.'_1';
            $nr_2=$i.'_2';
            $nr_3=$i.'_3';
            $nr_5=$i.'_5';

            if (isset($_SESSION[$nr_0])){
                $item['nr'] = $_SESSION[$nr_0];
                $item['id_prod'] = $_SESSION[$nr_1];
                $item['ile_sztuk'] = $_SESSION[$nr_2];
                $item['wielkosc'] = $_SESSION[$nr_3];
                $item['price'] = $_SESSION[$nr_5];

                global $conn;
                $stmt = $conn->prepare("SELECT tytul FROM produkty WHERE id = ? LIMIT 1");
                $stmt->bind_param("i", $item['id_prod']);
                $stmt->execute();
               $result = $stmt->get_result();
               if(!$result){
                   continue;
                }
                 $product_data = $result->fetch_assoc();
                 $stmt->close();
                if($product_data){
                    $item['title'] = $product_data['tytul'];
                    $cart_items[] = $item;
                    $total_price += ($item['price'] * $item['ile_sztuk']);
                }

            }

        }
        if (empty($cart_items)) {
            return "<p>Koszyk jest pusty.</p>";
        }

        $wynik = "<h2>Koszyk</h2><ul style='list-style-type: none; padding: 0;'>";
        foreach($cart_items as $item){
            $wynik .= "<li style='padding:0.5rem; margin:0.5rem; border:1px solid #ccc; border-radius:4px; display:flex; align-items: center; justify-content: space-between;'>";
            $wynik .= "<div>";
            $wynik .= "<span style='font-weight: 700;'>".sanitize($item['title'])."</span>";
            $wynik .= " <span style='font-style: italic;'>Ilość: ".$item['ile_sztuk']." </span>";
            $wynik .= "</div>";
            $wynik .= "<div>";
            $wynik .= "Cena: ".formatPrice($item['price'] * $item['ile_sztuk']). " zł";
            $wynik .= "  <form method='post' style='display: inline-block; margin-left:1rem;'>
                    <input type='hidden' name='remove_item_nr' value='" . $item['nr'] . "'>
                      <button type='submit' name='remove_from_cart' class='submit-button' style='padding: 0.2rem 0.5rem; font-size: 0.9rem;'>Usuń z koszyka</button>
                   </form>";
            $wynik .= "</div>";
            $wynik .= "</li>";
        }
        $wynik .= "<li style='text-align: right; margin-top: 10px; padding: 10px; border-top: 1px solid #ccc;'>";
        $wynik .= "<b>Razem: ".formatPrice($total_price)." zł</b></li>";
        $wynik .= "</ul>";

        return $wynik;
    }
    return "<p>Koszyk jest pusty.</p>";
}

// Function to remove product from cart
function removeFromCart(){
    if (isset($_POST['remove_from_cart'])){
      $remove_item_nr = sanitize($_POST['remove_item_nr']);

        $nr_0=$remove_item_nr.'_0';
        $nr_1=$remove_item_nr.'_1';
        $nr_2=$remove_item_nr.'_2';
        $nr_3=$remove_item_nr.'_3';
        $nr_4=$remove_item_nr.'_4';
        $nr_5=$remove_item_nr.'_5';

        unset($_SESSION[$nr_0]);
        unset($_SESSION[$nr_1]);
        unset($_SESSION[$nr_2]);
        unset($_SESSION[$nr_3]);
        unset($_SESSION[$nr_4]);
        unset($_SESSION[$nr_5]);
    }
}
// Process actions
addToCart();
removeFromCart();

// Fetch page content
$page_alias = isset($_GET['alias']) ? sanitize($_GET['alias']) : 'glowna';
$page = fetchPage($conn, $page_alias);

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Dawid Koterwas" />
    <title>Machine Learning</title>
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
            integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body class="container" onload="startClock()">
<header class="main-header">
    <h1 class="site-title"><a href="index.php">Machine Learning</a></h1>
    <nav class="main-nav">
        <ul class="menu">
            <li><a href="index.php?alias=dl">Deep Learning</a></li>
            <li><a href="index.php?alias=nlp">NLP</a></li>
            <li><a href="index.php?alias=cv">Computer Vision</a></li>
            <li><a href="index.php?alias=rl">Reinforcement Learning</a></li>
            <li><a href="index.php?alias=etyka">Etyka AI</a></li>
             <li><a href="index.php?alias=filmy">Filmy</a></li>
        </ul>
    </nav>
</header>
<main class="content">
    <?php
    if ($page) {
        echo $page['page_content'];
    } else {
        echo "<p>Strona nie istnieje</p>";
    }
    ?>
    <?php echo displayCategoriesFront($conn); ?>
    <?php echo displayProductsFront($conn); ?>
   <?php echo showCart(); ?>
    <?php PokazKontakt(); ?>
</main>
<footer class="main-footer">
    <?php
    $nrIndeksu = 169257;
    $nrGrupy = 2;
    echo "Autor: Dawid Koterwas " . $nrIndeksu . " grupa " . $nrGrupy . "<br/><br/>";
    ?>
</footer>
</body>
</html>
<?php
$conn->close();
?>