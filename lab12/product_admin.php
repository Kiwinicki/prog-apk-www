<?php
include_once 'cfg.php';
include_once 'helpers.php';

// Function to display the add new category form
function displayAddCategoryForm($conn) {
    if (isset($_POST['dodaj_kategoria'])) {
        $matka = intval($_POST['matka']);
        $nazwa = sanitize($_POST['nazwa'] ?? '');

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

// Function to display the edit category form
function displayEditCategoryForm($conn, $id) {
    $kategoria = fetchCategoryById($conn, $id);
    if (!$kategoria) {
        return "<p>Nie znaleziono kategorii o ID: " . $id . "</p>";
    }
    if (isset($_POST['edytuj_kategoria'])) {
        $matka = intval($_POST['matka']);
        $nazwa = sanitize($_POST['nazwa'] ?? '');

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
    <input type='text' name='nazwa' id='nazwa' value='" . sanitize($kategoria['nazwa'] ?? '') . "'><br><br>

    <input type='submit' name='edytuj_kategoria' value='Zapisz zmiany'>
    </form>";
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
        $tytul = sanitize($_POST['tytul'] ?? '');
        $opis = sanitize($_POST['opis'] ?? '');
        $cena_netto = floatval($_POST['cena_netto']);
        $podatek_vat = floatval($_POST['podatek_vat']);
        $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
        $kategoria_id = intval($_POST['kategoria_id']);
        $gabaryt = sanitize($_POST['gabaryt'] ?? '');
        $zdjecie = sanitize($_POST['zdjecie'] ?? '');

       //removed status_dostepnosci from query
        $stmt = $conn->prepare("INSERT INTO produkty (tytul, opis, cena_netto, podatek_vat, ilosc_sztuk, kategoria_id, gabaryt, zdjecie) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdidsiss", $tytul, $opis, $cena_netto, $podatek_vat, $ilosc_sztuk, $kategoria_id, $gabaryt, $zdjecie);
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

// Function to display the edit product form
function displayEditProductForm($conn, $id) {
      $produkt = fetchProductById($conn, $id);
       if (!$produkt) {
        return "<p>Nie znaleziono produktu o ID: " . $id . "</p>";
       }
    if (isset($_POST['edytuj_produkt'])) {
        $tytul = sanitize($_POST['tytul'] ?? '');
        $opis = sanitize($_POST['opis'] ?? '');
        $cena_netto = floatval($_POST['cena_netto']);
        $podatek_vat = floatval($_POST['podatek_vat']);
        $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
        $kategoria_id = intval($_POST['kategoria_id']);
        $gabaryt = sanitize($_POST['gabaryt'] ?? '');
        $zdjecie = sanitize($_POST['zdjecie'] ?? '');

         //removed status_dostepnosci from query
        $stmt = $conn->prepare("UPDATE produkty SET tytul = ?, opis = ?, cena_netto = ?, podatek_vat = ?, ilosc_sztuk = ?,  kategoria_id = ?, gabaryt = ?, zdjecie = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssdidsissi", $tytul, $opis, $cena_netto, $podatek_vat, $ilosc_sztuk,  $kategoria_id, $gabaryt, $zdjecie, $id);
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
        <input type='text' name='tytul' id='tytul' value='" . sanitize($produkt['tytul'] ?? '') . "'><br><br>

        <label for='opis'>Opis:</label><br>
        <textarea name='opis' id='opis' rows='5' cols='50'>" . sanitize($produkt['opis'] ?? '') . "</textarea><br><br>

        <label for='cena_netto'>Cena netto:</label><br>
        <input type='number' name='cena_netto' id='cena_netto' step='0.01' value='" . $produkt['cena_netto'] . "'><br><br>
          <label for='podatek_vat'>Podatek VAT:</label><br>
            <input type='number' name='podatek_vat' id='podatek_vat' step='0.01' value='" . $produkt['podatek_vat'] . "'><br><br>

        <label for='ilosc_sztuk'>Ilość sztuk:</label><br>
        <input type='number' name='ilosc_sztuk' id='ilosc_sztuk' value='" . $produkt['ilosc_sztuk'] . "'><br><br>


         <label for='kategoria_id'>Kategoria:</label><br>
            <select name='kategoria_id' id='kategoria_id'>
              ".$kategorie."
             </select><br><br>
             
              <label for='gabaryt'>Gabaryt:</label><br>
             <input type='text' name='gabaryt' id='gabaryt' value='" . sanitize($produkt['gabaryt'] ?? '') . "'><br><br>
                
          <label for='zdjecie'>Zdjęcie (Link):</label><br>
             <input type='text' name='zdjecie' id='zdjecie' value='" . sanitize($produkt['zdjecie'] ?? '') . "'><br><br>
        
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
             "<a href='?akcja=produkt_edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
                "<a href='?akcja=produkt_usun&id=" . $row['id'] . "'>Usuń</a></li>";
    }
     $wynik .= "</ul>";
    $wynik .= "<br><a href='?akcja=produkt_dodaj'>Dodaj nowy produkt</a>";
    return $wynik;
}
?>