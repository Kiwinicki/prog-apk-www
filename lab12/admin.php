<?php
// Enable error reporting for debugging (remove for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'cfg.php';

session_start();


// Establish database connection
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize user input
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''));
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

// Function to fetch all pages
function fetchAllPages($conn) {
    $query = "SELECT id, page_title FROM page_list LIMIT 100";
    $result = $conn->query($query);

    if (!$result) {
        return null; // Return null if the query fails
    }

    $pages = [];
    while ($row = $result->fetch_assoc()) {
        $pages[] = $row;
    }
    return $pages;
}

// Function to display the list of pages with edit/delete options
function displayPageList($conn) {
    $pages = fetchAllPages($conn);
    if (!$pages){
       return  "Database error: " . $conn->error;
    }
    $wynik = "<ul>";
    foreach($pages as $row){
          $wynik .= "<li>ID: " . $row['id'] . " | Tytuł: " . sanitize($row['page_title']) .
            " | <a href='?akcja=edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
            "<a href='?akcja=usun&id=" . $row['id'] . "'>Usuń</a></li>";
    }
    $wynik .= "</ul>";
    $wynik .= "<br><a href='?akcja=dodaj'>Dodaj nową podstronę</a>";
    return $wynik;
}

// Function to fetch a single page by id
function fetchPageById($conn, $id){
        $stmt = $conn->prepare("SELECT * FROM page_list WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false || $result->num_rows === 0) {
            return null;
         }
        $page = $result->fetch_assoc();
           $stmt->close();
         return $page;
}

// Function to display the edit page form
function displayEditPageForm($conn, $id) {
    $page = fetchPageById($conn, $id);
     if (!$page) {
            return "<p>Nie znaleziono podstrony o ID: " . $id . "</p>";
        }

    if (isset($_POST['edytuj_podstrone'])) {
        $tytul = sanitize($_POST['tytul']);
        $tresc = $_POST['page_content'];
        $alias = sanitize($_POST['alias']);
        $status = isset($_POST['status']) ? 1 : 0;

       $stmt = $conn->prepare("UPDATE page_list SET page_title = ?, page_content = ?, alias = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssssi", $tytul, $tresc, $alias, $status, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return "<p>Podstrona zaktualizowana!</p>";
        } else {
           $stmt->close();
            return "<p>Błąd aktualizacji podstrony: " . $conn->error . "</p>";
        }
    }

    $wynik = "
    <h2>Edytuj podstronę</h2>
    <form method='post'>
        <label for='tytul'>Tytuł:</label><br>
        <input type='text' name='tytul' id='tytul' value='" . sanitize($page['page_title']) . "'><br><br>

        <label for='page_content'>Treść:</label><br>
        <textarea name='page_content' id='page_content' rows='10' cols='50'>" . sanitize($page['page_content']) . "</textarea><br><br>

        <label for='alias'>Alias:</label><br>
        <input type='text' name='alias' id='alias' value='" . sanitize($page['alias']) . "'><br><br>

        <input type='checkbox' name='status' id='status' value='1' " . ($page['status'] ? 'checked' : '') . ">
        <label for='status'>Aktywna</label><br><br>

        <input type='submit' name='edytuj_podstrone' value='Zapisz zmiany'>
    </form>";

    return $wynik;
}
// Function to display the add new page form
function displayAddPageForm($conn) {
    if (isset($_POST['dodaj_podstrone'])) {
       $tytul = sanitize($_POST['page_title']);
       $tresc = $_POST['page_content'];
       $alias = sanitize($_POST['alias']);
       $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO page_list (page_title, page_content, alias, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $tytul, $tresc, $alias, $status);

        if ($stmt->execute()) {
            $stmt->close();
            return "<p>Podstrona dodana!</p>";
        } else {
             $stmt->close();
            return "<p>Błąd dodawania podstrony: " . $conn->error . "</p>";
        }
    }

    $wynik = "
    <h2>Dodaj nową podstronę</h2>
    <form method='post'>
    <label for='page_title'>Tytuł:</label><br>
    <input type='text' name='page_title' id='page_title'><br><br>
    
    <label for='alias'>Alias:</label><br>
    <input type='text' name='alias' id='alias'><br><br>

    <label for='page_content'>Treść:</label><br>
    <textarea name='page_content' id='page_content' rows='10' cols='50'></textarea><br><br>

    <input type='checkbox' name='status' id='status' value='1' checked>
    <label for='status'>Aktywna</label><br><br>

    <input type='submit' name='dodaj_podstrone' value='Dodaj'>
    </form>";
    return $wynik;
}
// Function to delete a page
function deletePage($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        $stmt->close();
        return "Usunięto podstronę o ID: " . $id;
    } else {
      $stmt->close();
        return "Błąd usuwania podstrony: " . $conn->error;
    }
}
// Function to display the add new category form
function displayAddCategoryForm($conn) {
      if (isset($_POST['dodaj_kategoria'])) {
        $matka = intval($_POST['matka']);
        $nazwa = sanitize($_POST['nazwa']);

        $stmt = $conn->prepare("INSERT INTO kategorie (matka, nazwa) VALUES (?, ?)");
        $stmt->bind_param("is", $matka, $nazwa);

        if ($stmt->execute()) {
           $stmt->close();
            return "<p>Kategoria dodana!</p>";
        } else {
            $stmt->close();
            return "<p>Błąd dodawania kategorii: " . $conn->error . "</p>";
        }
    }

    $wynik = "
    <h2>Dodaj nową kategorię</h2>
    <form method='post'>
    <label for='matka'>Matka (ID):</label><br>
    <input type='number' name='matka' id='matka' value='0'><br><br>

    <label for='nazwa'>Nazwa:</label><br>
    <input type='text' name='nazwa' id='nazwa'><br><br>

    <input type='submit' name='dodaj_kategoria' value='Dodaj'>
    </form>";
    return $wynik;
}

// Function to delete a category
function deleteCategory($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM kategorie WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
       $stmt->close();
        return "Usunięto kategorię o ID: " . $id;
    } else {
         $stmt->close();
        return "Błąd usuwania kategorii: " . $conn->error;
    }
}
// Function to fetch a single category by id
function fetchCategoryById($conn, $id){
       $stmt = $conn->prepare("SELECT * FROM kategorie WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false || $result->num_rows === 0) {
            return null;
        }
        $kategoria = $result->fetch_assoc();
          $stmt->close();
        return $kategoria;
}
// Function to display the edit category form
function displayEditCategoryForm($conn, $id) {
    $kategoria = fetchCategoryById($conn, $id);
    if (!$kategoria) {
        return "<p>Nie znaleziono kategorii o ID: " . $id . "</p>";
    }
    if (isset($_POST['edytuj_kategoria'])) {
        $matka = intval($_POST['matka']);
        $nazwa = sanitize($_POST['nazwa']);

        $stmt = $conn->prepare("UPDATE kategorie SET matka = ?, nazwa = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("isi", $matka, $nazwa, $id);

        if ($stmt->execute()) {
            $stmt->close();
            return "<p>Kategoria zaktualizowana!</p>";
        } else {
            $stmt->close();
            return "<p>Błąd aktualizacji kategorii: " . $conn->error . "</p>";
        }
    }

    $wynik = "
    <h2>Edytuj kategorię</h2>
    <form method='post'>
    <label for='matka'>Matka (ID):</label><br>
    <input type='number' name='matka' id='matka' value='" . $kategoria['matka'] . "'><br><br>

    <label for='nazwa'>Nazwa:</label><br>
    <input type='text' name='nazwa' id='nazwa' value='" . sanitize($kategoria['nazwa']) . "'><br><br>

    <input type='submit' name='edytuj_kategoria' value='Zapisz zmiany'>
    </form>";
    return $wynik;
}

// Function to generate the category tree
 function generateCategoryTree($conn, $parent = 0, $level = 0) {
        $wynik = "<ul>";
        $stmt = $conn->prepare("SELECT * FROM kategorie WHERE matka = ?");
        $stmt->bind_param("i", $parent);
        $stmt->execute();
         $result = $stmt->get_result();
          if (!$result) {
            return "Database error: " . $conn->error;
        }
        while ($row = $result->fetch_assoc()) {
            $wynik .= "<li>" . str_repeat("   ", $level) .
            "ID: " . $row['id'] . " | " .
            "Nazwa: " . sanitize($row['nazwa']) . " | " .
            "Matka: " . $row['matka'] . " | " .
            "<a href='?akcja=kategorie_edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
            "<a href='?akcja=kategorie_usun&id=" . $row['id'] . "'>Usuń</a>";
            $wynik .= generateCategoryTree($conn, $row['id'], $level + 1);
            $wynik .= "</li>";
        }
        $wynik .= "</ul>";
         $stmt->close();
        return $wynik;
    }

// Function to display the category list with edit/delete options
function displayCategoryList($conn) {
        $categoryTree = generateCategoryTree($conn);
    return "<h2>Lista Kategorii</h2>" . $categoryTree . "<br><a href='?akcja=kategorie_dodaj'>Dodaj nową kategorię</a>";
}
// Function to display the add new product form
function displayAddProductForm($conn) {
    if (isset($_POST['dodaj_produkt'])) {
        $tytul = sanitize($_POST['tytul']);
        $opis = sanitize($_POST['opis']);
        $cena_netto = floatval($_POST['cena_netto']);
        $podatek_vat = floatval($_POST['podatek_vat']);
        $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
        $status_dostepnosci = sanitize($_POST['status_dostepnosci']);
        $kategoria_id = intval($_POST['kategoria_id']);
        $gabaryt = sanitize($_POST['gabaryt']);
        $zdjecie = sanitize($_POST['zdjecie']);

        $stmt = $conn->prepare("INSERT INTO produkty (tytul, opis, cena_netto, podatek_vat, ilosc_sztuk, status_dostepnosci, kategoria_id, gabaryt, zdjecie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdidsiss", $tytul, $opis, $cena_netto, $podatek_vat, $ilosc_sztuk, $status_dostepnosci, $kategoria_id, $gabaryt, $zdjecie);
        if ($stmt->execute()) {
            $stmt->close();
            return "<p>Produkt dodany!</p>";
        } else {
            $stmt->close();
            return "<p>Błąd dodawania produktu: " . $conn->error . "</p>";
        }
    }
    $kategorie = fetchCategoriesForDropdown($conn);
    $wynik = "
        <h2>Dodaj nowy produkt</h2>
        <form method='post'>
            <label for='tytul'>Tytuł:</label><br>
            <input type='text' name='tytul' id='tytul'><br><br>

            <label for='opis'>Opis:</label><br>
            <textarea name='opis' id='opis' rows='5' cols='50'></textarea><br><br>

            <label for='cena_netto'>Cena netto:</label><br>
            <input type='number' name='cena_netto' id='cena_netto' step='0.01'><br><br>
            
            <label for='podatek_vat'>Podatek VAT:</label><br>
            <input type='number' name='podatek_vat' id='podatek_vat' step='0.01' value = '0.23'><br><br>

            <label for='ilosc_sztuk'>Ilość sztuk:</label><br>
            <input type='number' name='ilosc_sztuk' id='ilosc_sztuk' value='0'><br><br>
            
             <label for='status_dostepnosci'>Status dostępności:</label><br>
            <select name='status_dostepnosci' id='status_dostepnosci'>
                <option value='dostepny'>Dostępny</option>
                <option value='niedostepny'>Niedostępny</option>
                 <option value='w_przygotowaniu' selected>W przygotowaniu</option>
            </select><br><br>

            <label for='kategoria_id'>Kategoria:</label><br>
            <select name='kategoria_id' id='kategoria_id'>
              ".$kategorie."
             </select><br><br>

            <label for='gabaryt'>Gabaryt:</label><br>
             <input type='text' name='gabaryt' id='gabaryt'><br><br>
             
             <label for='zdjecie'>Zdjęcie (Link):</label><br>
             <input type='text' name='zdjecie' id='zdjecie'><br><br>

            <input type='submit' name='dodaj_produkt' value='Dodaj'>
        </form>";
    return $wynik;
}

// Function to delete a product
function deleteProduct($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM produkty WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
       $stmt->close();
        return "Usunięto produkt o ID: " . $id;
    } else {
        $stmt->close();
        return "Błąd usuwania produktu: " . $conn->error;
    }
}

// Function to fetch a single product by ID
function fetchProductById($conn, $id){
        $stmt = $conn->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
    if ($result === false || $result->num_rows === 0) {
           return null;
     }
        $produkt = $result->fetch_assoc();
         $stmt->close();
        return $produkt;
}
// Function to display the edit product form
function displayEditProductForm($conn, $id) {
      $produkt = fetchProductById($conn, $id);
       if (!$produkt) {
        return "<p>Nie znaleziono produktu o ID: " . $id . "</p>";
       }
    if (isset($_POST['edytuj_produkt'])) {
        $tytul = sanitize($_POST['tytul']);
        $opis = sanitize($_POST['opis']);
        $cena_netto = floatval($_POST['cena_netto']);
        $podatek_vat = floatval($_POST['podatek_vat']);
        $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
        $status_dostepnosci = sanitize($_POST['status_dostepnosci']);
        $kategoria_id = intval($_POST['kategoria_id']);
        $gabaryt = sanitize($_POST['gabaryt']);
        $zdjecie = sanitize($_POST['zdjecie']);
        $stmt = $conn->prepare("UPDATE produkty SET tytul = ?, opis = ?, cena_netto = ?, podatek_vat = ?, ilosc_sztuk = ?, status_dostepnosci = ?, kategoria_id = ?, gabaryt = ?, zdjecie = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssdidsissi", $tytul, $opis, $cena_netto, $podatek_vat, $ilosc_sztuk, $status_dostepnosci, $kategoria_id, $gabaryt, $zdjecie, $id);
        if ($stmt->execute()) {
            $stmt->close();
            return "<p>Produkt zaktualizowany!</p>";
        } else {
            $stmt->close();
             return "<p>Błąd aktualizacji produktu: " . $conn->error . "</p>";
        }
    }
    $kategorie = fetchCategoriesForDropdown($conn);
     $wynik = "
    <h2>Edytuj produkt</h2>
    <form method='post'>
        <label for='tytul'>Tytuł:</label><br>
        <input type='text' name='tytul' id='tytul' value='" . sanitize($produkt['tytul']) . "'><br><br>

        <label for='opis'>Opis:</label><br>
        <textarea name='opis' id='opis' rows='5' cols='50'>" . sanitize($produkt['opis']) . "</textarea><br><br>

        <label for='cena_netto'>Cena netto:</label><br>
        <input type='number' name='cena_netto' id='cena_netto' step='0.01' value='" . $produkt['cena_netto'] . "'><br><br>
          <label for='podatek_vat'>Podatek VAT:</label><br>
            <input type='number' name='podatek_vat' id='podatek_vat' step='0.01' value='" . $produkt['podatek_vat'] . "'><br><br>

        <label for='ilosc_sztuk'>Ilość sztuk:</label><br>
        <input type='number' name='ilosc_sztuk' id='ilosc_sztuk' value='" . $produkt['ilosc_sztuk'] . "'><br><br>

          <label for='status_dostepnosci'>Status dostępności:</label><br>
            <select name='status_dostepnosci' id='status_dostepnosci'>
                <option value='dostepny' ".($produkt['status_dostepnosci'] == 'dostepny' ? 'selected' : '')." >Dostępny</option>
                <option value='niedostepny' ".($produkt['status_dostepnosci'] == 'niedostepny' ? 'selected' : '').">Niedostępny</option>
                 <option value='w_przygotowaniu' ".($produkt['status_dostepnosci'] == 'w_przygotowaniu' ? 'selected' : '').">W przygotowaniu</option>
            </select><br><br>

         <label for='kategoria_id'>Kategoria:</label><br>
            <select name='kategoria_id' id='kategoria_id'>
              ".$kategorie."
             </select><br><br>
             
              <label for='gabaryt'>Gabaryt:</label><br>
             <input type='text' name='gabaryt' id='gabaryt' value='" . sanitize($produkt['gabaryt']) . "'><br><br>
                
          <label for='zdjecie'>Zdjęcie (Link):</label><br>
             <input type='text' name='zdjecie' id='zdjecie' value='" . sanitize($produkt['zdjecie']) . "'><br><br>
        
        <input type='submit' name='edytuj_produkt' value='Zapisz zmiany'>
    </form>";
    return $wynik;
}

// Function to fetch products with category names
function fetchProducts($conn) {
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

// Function to display the product list with edit/delete options
function displayProductList($conn) {
    $products = fetchProducts($conn);
    if (!$products){
      return  "Database error: " . $conn->error;
    }
     $wynik = "<h2>Lista Produktów</h2><ul>";
      foreach ($products as $row){
           $wynik .= "<li>ID: " . $row['id'] . " | " .
            "Tytuł: " . sanitize($row['tytul']) . " | " .
            "Cena: " . $row['cena_netto'] . " | " .
            "Kategoria: " . sanitize($row['kategoria_nazwa']) . " | " .
            "Status: " . sanitize($row['status_dostepnosci']) . " | " .
             "<a href='?akcja=produkt_edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
                "<a href='?akcja=produkt_usun&id=" . $row['id'] . "'>Usuń</a></li>";
    }
     $wynik .= "</ul>";
    $wynik .= "<br><a href='?akcja=produkt_dodaj'>Dodaj nowy produkt</a>";
    return $wynik;
}


// Function to fetch categories for a dropdown list
function fetchCategoriesForDropdown($conn) {
       $query = "SELECT id, nazwa FROM kategorie LIMIT 100";
       $result = $conn->query($query);
       if (!$result) {
        return null; // Return null if the query fails
    }
        $kategorie = "";
      while ($row = $result->fetch_assoc()) {
          $kategorie .= "<option value='" . $row['id'] . "'>" . sanitize($row['nazwa']) . "</option>";
      }
       return $kategorie;
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