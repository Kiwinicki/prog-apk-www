<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Keep session_start here, it's the entry point
include_once 'cfg.php';
include_once 'helpers.php';
include_once 'page_admin.php';
include_once 'product_admin.php';


// Establish database connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to display the login form
function displayLoginForm() {
    return '
    <div class="logowanie">
        <h1 class="heading">Panel CMS:</h1>
        <div class="logowanie">
            <form method="post" name="LoginForm" action="">
                <table class="logowanie">
                    <tr><td class="log4_t">Login:</td><td><input type="text" name="login_email" class="logowanie" /></td></tr>
                    <tr><td class="log4_t">Hasło:</td><td><input type="password" name="login_pass" class="logowanie" /></td></tr>
                    <tr><td> </td><td><input type="submit" name="x1_submit" class="logowanie" value="Zaloguj" /></td></tr>
                </table>
            </form>
        </div>
    </div>';
}
// Handle login
if (isset($_POST['x1_submit'])) {
    if ($_POST['login_email'] == $GLOBALS['login'] && $_POST['login_pass'] == $GLOBALS['pass']) {
        $_SESSION['zalogowany'] = true;
    } else {
        echo "<p style='color:red;'>Błędny login lub hasło!</p>";
        echo displayLoginForm();
        exit();
    }
}

if (!isset($_SESSION['zalogowany'])) {
    echo displayLoginForm();
    exit();
}

// Handle actions based on the 'akcja' parameter
if (isset($_GET['akcja'])) {
    $akcja = $_GET['akcja'];
    if ($akcja == 'lista') {
        echo displayPageList($conn);
    } elseif ($akcja == 'edytuj' && isset($_GET['id'])) {
        echo displayEditPageForm($conn, $_GET['id']);
    } elseif ($akcja == 'dodaj') {
        echo displayAddPageForm($conn);
    } elseif ($akcja == 'usun' && isset($_GET['id'])) {
        echo deletePage($conn, $_GET['id']);
    }
     // Category Management actions
    elseif ($akcja == 'kategorie_lista') {
        echo displayCategoryList($conn);
    } elseif ($akcja == 'kategorie_dodaj') {
        echo displayAddCategoryForm($conn);
    } elseif ($akcja == 'kategorie_edytuj' && isset($_GET['id'])) {
        echo displayEditCategoryForm($conn, $_GET['id']);
    } elseif ($akcja == 'kategorie_usun' && isset($_GET['id'])) {
        echo deleteCategory($conn, $_GET['id']);
    }
    // Product Management actions
     elseif ($akcja == 'produkt_lista') {
        echo displayProductList($conn);
    } elseif ($akcja == 'produkt_dodaj') {
        echo displayAddProductForm($conn);
    } elseif ($akcja == 'produkt_edytuj' && isset($_GET['id'])) {
        echo displayEditProductForm($conn, $_GET['id']);
    } elseif ($akcja == 'produkt_usun' && isset($_GET['id'])) {
         echo deleteProduct($conn, $_GET['id']);
    }
} else {
    echo displayPageList($conn);
}
echo "<br><br><a href='?akcja=kategorie_lista'>Zarządzaj Kategoriami</a>";
echo "<br><br><a href='?akcja=produkt_lista'>Zarządzaj Produktami</a>";

$conn->close();
?>