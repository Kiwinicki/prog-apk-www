<?php

include_once 'cfg.php';

session_start();

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function FormularzLogowania() {
    $wynik = '
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
    return $wynik;
}

function ListaPodstron($conn) {
    $query = "SELECT id, page_title FROM page_list LIMIT 100";
    $result = $conn->query($query);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $wynik = "<ul>";
    while ($row = $result->fetch_assoc()) {
        $wynik .= "<li>ID: " . $row['id'] . " | Tytuł: " . htmlspecialchars($row['page_title'] ?? '') .
            " | <a href='?akcja=edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
            "<a href='?akcja=usun&id=" . $row['id'] . "'>Usuń</a></li>";
    }
    $wynik .= "</ul>";
    $wynik .= "<br><a href='?akcja=dodaj'>Dodaj nową podstronę</a>";
    return $wynik;
}

function EdytujPodstrone($conn, $id) {
    $id = intval($id);

    $stmt = $conn->prepare("SELECT * FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $podstrona = $result->fetch_assoc();

    if (!$podstrona) {
        return "<p>Nie znaleziono podstrony o ID: " . $id . "</p>";
    }

    if (isset($_POST['edytuj_podstrone'])) {
        $tytul = htmlspecialchars($_POST['tytul'] ?? '');
        $tresc = $_POST['page_content'] ?? '';
        $alias = htmlspecialchars($_POST['alias'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE page_list SET page_title = ?, page_content = ?, alias = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssssi", $tytul, $tresc, $alias, $status, $id);

        if ($stmt->execute()) {
            return "<p>Podstrona zaktualizowana!</p>";
        } else {
            return "<p>Błąd aktualizacji podstrony: " . $conn->error . "</p>";
        }
    }

    $wynik = "
    <h2>Edytuj podstronę</h2>
    <form method='post'>
        <label for='tytul'>Tytuł:</label><br>
        <input type='text' name='tytul' id='tytul' value='" . htmlspecialchars($podstrona['page_title'] ?? '') . "'><br><br>

        <label for='page_content'>Treść:</label><br>
        <textarea name='page_content' id='page_content' rows='10' cols='50'>" . htmlspecialchars($podstrona['page_content'] ?? '') . "</textarea><br><br>

        <label for='alias'>Alias:</label><br>
        <input type='text' name='alias' id='alias' value='" . htmlspecialchars($podstrona['alias'] ?? '') . "'><br><br>

        <input type='checkbox' name='status' id='status' value='1' " . ($podstrona['status'] ? 'checked' : '') . ">
        <label for='status'>Aktywna</label><br><br>

        <input type='submit' name='edytuj_podstrone' value='Zapisz zmiany'>
    </form>";

    return $wynik;
}

function DodajNowaPodstrone($conn) {
    if (isset($_POST['dodaj_podstrone'])) {
        $tytul = htmlspecialchars($_POST['page_title'] ?? '');
        $tresc = $_POST['page_content'] ?? '';
        $alias = htmlspecialchars($_POST['alias'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO page_list (page_title, page_content, alias, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $tytul, $tresc, $alias, $status);

        if ($stmt->execute()) {
            return "<p>Podstrona dodana!</p>";
        } else {
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

function UsunPodstrone($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        return "Usunięto podstronę o ID: " . $id;
    } else {
        return "Błąd usuwania podstrony: " . $conn->error;
    }
}

// Category Management Functions
function DodajKategorie($conn) {
    if (isset($_POST['dodaj_kategoria'])) {
        $matka = intval($_POST['matka']);
        $nazwa = htmlspecialchars($_POST['nazwa'] ?? '');

        $stmt = $conn->prepare("INSERT INTO kategorie (matka, nazwa) VALUES (?, ?)");
        $stmt->bind_param("is", $matka, $nazwa);

        if ($stmt->execute()) {
            return "<p>Kategoria dodana!</p>";
        } else {
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

function UsunKategorie($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM kategorie WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        return "Usunięto kategorię o ID: " . $id;
    } else {
        return "Błąd usuwania kategorii: " . $conn->error;
    }
}

function EdytujKategorie($conn, $id) {
    $id = intval($id);

    $stmt = $conn->prepare("SELECT * FROM kategorie WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kategoria = $result->fetch_assoc();

    if (!$kategoria) {
        return "<p>Nie znaleziono kategorii o ID: " . $id . "</p>";
    }

    if (isset($_POST['edytuj_kategoria'])) {
        $matka = intval($_POST['matka']);
        $nazwa = htmlspecialchars($_POST['nazwa'] ?? '');

        $stmt = $conn->prepare("UPDATE kategorie SET matka = ?, nazwa = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("isi", $matka, $nazwa, $id);

        if ($stmt->execute()) {
            return "<p>Kategoria zaktualizowana!</p>";
        } else {
            return "<p>Błąd aktualizacji kategorii: " . $conn->error . "</p>";
        }
    }

    $wynik = "
    <h2>Edytuj kategorię</h2>
    <form method='post'>
    <label for='matka'>Matka (ID):</label><br>
    <input type='number' name='matka' id='matka' value='" . $kategoria['matka'] . "'><br><br>

    <label for='nazwa'>Nazwa:</label><br>
    <input type='text' name='nazwa' id='nazwa' value='" . htmlspecialchars($kategoria['nazwa'] ?? '') . "'><br><br>

    <input type='submit' name='edytuj_kategoria' value='Zapisz zmiany'>
    </form>";
    return $wynik;
}

function PokazKategorie($conn) {
     function generateCategoryTree($conn, $parent = 0, $level = 0) {
        $wynik = "<ul>";
        $stmt = $conn->prepare("SELECT * FROM kategorie WHERE matka = ?");
        $stmt->bind_param("i", $parent);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
             $wynik .= "<li>" . str_repeat("   ", $level) . 
             "ID: " . $row['id'] . " | " .
                "Nazwa: " . htmlspecialchars($row['nazwa'] ?? '') . " | " .
                "Matka: " . $row['matka'] . " | " .
                "<a href='?akcja=kategorie_edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
                "<a href='?akcja=kategorie_usun&id=" . $row['id'] . "'>Usuń</a>";
            $wynik .= generateCategoryTree($conn, $row['id'], $level + 1);
              $wynik .= "</li>";
        }
          $wynik .= "</ul>";
        return $wynik;
    }

    $wynik = "<h2>Lista Kategorii</h2>";
    $wynik .= generateCategoryTree($conn);
    $wynik .= "<br><a href='?akcja=kategorie_dodaj'>Dodaj nową kategorię</a>";
    return $wynik;
}
// Product Management Functions

function DodajProdukt($conn) {
    if (isset($_POST['dodaj_produkt'])) {
        $tytul = htmlspecialchars($_POST['tytul'] ?? '');
        $opis = htmlspecialchars($_POST['opis'] ?? '');
        $cena_netto = floatval($_POST['cena_netto']);
        $podatek_vat = floatval($_POST['podatek_vat']);
        $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
        $status_dostepnosci = htmlspecialchars($_POST['status_dostepnosci'] ?? '');
        $kategoria_id = intval($_POST['kategoria_id']);
        $gabaryt = htmlspecialchars($_POST['gabaryt'] ?? '');
          $zdjecie = htmlspecialchars($_POST['zdjecie'] ?? '');


       $stmt = $conn->prepare("INSERT INTO produkty (tytul, opis, cena_netto, podatek_vat, ilosc_sztuk, status_dostepnosci, kategoria_id, gabaryt, zdjecie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdidsiss", $tytul, $opis, $cena_netto, $podatek_vat, $ilosc_sztuk, $status_dostepnosci, $kategoria_id, $gabaryt, $zdjecie);
    
        if ($stmt->execute()) {
            return "<p>Produkt dodany!</p>";
        } else {
            return "<p>Błąd dodawania produktu: " . $conn->error . "</p>";
        }
    }
    $kategorie = PobierzKategorieZListy($conn);
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

function UsunProdukt($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM produkty WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        return "Usunięto produkt o ID: " . $id;
    } else {
        return "Błąd usuwania produktu: " . $conn->error;
    }
}

function EdytujProdukt($conn, $id) {
        $id = intval($id);

    $stmt = $conn->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $produkt = $result->fetch_assoc();

    if (!$produkt) {
        return "<p>Nie znaleziono produktu o ID: " . $id . "</p>";
    }

    if (isset($_POST['edytuj_produkt'])) {
         $tytul = htmlspecialchars($_POST['tytul'] ?? '');
        $opis = htmlspecialchars($_POST['opis'] ?? '');
        $cena_netto = floatval($_POST['cena_netto']);
        $podatek_vat = floatval($_POST['podatek_vat']);
        $ilosc_sztuk = intval($_POST['ilosc_sztuk']);
        $status_dostepnosci = htmlspecialchars($_POST['status_dostepnosci'] ?? '');
        $kategoria_id = intval($_POST['kategoria_id']);
        $gabaryt = htmlspecialchars($_POST['gabaryt'] ?? '');
           $zdjecie = htmlspecialchars($_POST['zdjecie'] ?? '');


       $stmt = $conn->prepare("UPDATE produkty SET tytul = ?, opis = ?, cena_netto = ?, podatek_vat = ?, ilosc_sztuk = ?, status_dostepnosci = ?, kategoria_id = ?, gabaryt = ?, zdjecie = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssdidsissi", $tytul, $opis, $cena_netto, $podatek_vat, $ilosc_sztuk, $status_dostepnosci, $kategoria_id, $gabaryt, $zdjecie, $id);
   
        if ($stmt->execute()) {
            return "<p>Produkt zaktualizowany!</p>";
        } else {
            return "<p>Błąd aktualizacji produktu: " . $conn->error . "</p>";
        }
    }
    $kategorie = PobierzKategorieZListy($conn);
     $wynik = "
    <h2>Edytuj produkt</h2>
    <form method='post'>
        <label for='tytul'>Tytuł:</label><br>
        <input type='text' name='tytul' id='tytul' value='" . htmlspecialchars($produkt['tytul'] ?? '') . "'><br><br>

        <label for='opis'>Opis:</label><br>
        <textarea name='opis' id='opis' rows='5' cols='50'>" . htmlspecialchars($produkt['opis'] ?? '') . "</textarea><br><br>

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
             <input type='text' name='gabaryt' id='gabaryt' value='" . htmlspecialchars($produkt['gabaryt'] ?? '') . "'><br><br>
                
          <label for='zdjecie'>Zdjęcie (Link):</label><br>
             <input type='text' name='zdjecie' id='zdjecie' value='" . htmlspecialchars($produkt['zdjecie'] ?? '') . "'><br><br>
        
        <input type='submit' name='edytuj_produkt' value='Zapisz zmiany'>
    </form>";
    return $wynik;
}

function PokazProdukty($conn) {
    $query = "SELECT p.*, k.nazwa as kategoria_nazwa FROM produkty p LEFT JOIN kategorie k ON p.kategoria_id = k.id LIMIT 100";
     $result = $conn->query($query);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
     $wynik = "<h2>Lista Produktów</h2><ul>";
      while ($row = $result->fetch_assoc()) {
        $wynik .= "<li>ID: " . $row['id'] . " | " .
            "Tytuł: " . htmlspecialchars($row['tytul'] ?? '') . " | " .
            "Cena: " . $row['cena_netto'] . " | " .
            "Kategoria: " . htmlspecialchars($row['kategoria_nazwa'] ?? '') . " | " .
            "Status: " . htmlspecialchars($row['status_dostepnosci'] ?? '') . " | " .
             "<a href='?akcja=produkt_edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
                "<a href='?akcja=produkt_usun&id=" . $row['id'] . "'>Usuń</a></li>";
    }
     $wynik .= "</ul>";
    $wynik .= "<br><a href='?akcja=produkt_dodaj'>Dodaj nowy produkt</a>";
    return $wynik;
}
function PobierzKategorieZListy($conn){
      $query = "SELECT id, nazwa FROM kategorie LIMIT 100";
       $result = $conn->query($query);
       if (!$result) {
        die("Query failed: " . $conn->error);
    }
        $kategorie = "";
      while ($row = $result->fetch_assoc()) {
          $kategorie .= "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nazwa'] ?? '') . "</option>";
      }
       return $kategorie;
}


// End of Product Management Functions
if (isset($_POST['x1_submit'])) {
    if ($_POST['login_email'] == $GLOBALS['login'] && $_POST['login_pass'] == $GLOBALS['pass']) {
        $_SESSION['zalogowany'] = true;
    } else {
        echo "<p style='color:red;'>Błędny login lub hasło!</p>";
        echo FormularzLogowania();
        exit();
    }
}

if (!isset($_SESSION['zalogowany'])) {
    echo FormularzLogowania();
    exit();
}

if (isset($_GET['akcja'])) {
    $akcja = $_GET['akcja'];
    if ($akcja == 'lista') {
        echo ListaPodstron($conn);
    } elseif ($akcja == 'edytuj' && isset($_GET['id'])) {
        echo EdytujPodstrone($conn, $_GET['id']);
    } elseif ($akcja == 'dodaj') {
        echo DodajNowaPodstrone($conn);
    } elseif ($akcja == 'usun' && isset($_GET['id'])) {
        echo UsunPodstrone($conn, $_GET['id']);
    }
    // Category Management actions
    elseif ($akcja == 'kategorie_lista') {
        echo PokazKategorie($conn);
    } elseif ($akcja == 'kategorie_dodaj') {
        echo DodajKategorie($conn);
    } elseif ($akcja == 'kategorie_edytuj' && isset($_GET['id'])) {
        echo EdytujKategorie($conn, $_GET['id']);
    } elseif ($akcja == 'kategorie_usun' && isset($_GET['id'])) {
        echo UsunKategorie($conn, $_GET['id']);
    }
    // Product Management actions
    elseif ($akcja == 'produkt_lista') {
        echo PokazProdukty($conn);
    } elseif ($akcja == 'produkt_dodaj') {
        echo DodajProdukt($conn);
    } elseif ($akcja == 'produkt_edytuj' && isset($_GET['id'])) {
        echo EdytujProdukt($conn, $_GET['id']);
    } elseif ($akcja == 'produkt_usun' && isset($_GET['id'])) {
        echo UsunProdukt($conn, $_GET['id']);
    }
} else {
    echo ListaPodstron($conn);
}
echo "<br><br><a href='?akcja=kategorie_lista'>Zarządzaj Kategoriami</a>";
echo "<br><br><a href='?akcja=produkt_lista'>Zarządzaj Produktami</a>";

$conn->close();
?>