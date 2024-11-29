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
        $wynik .= "<li>ID: " . $row['id'] . " | Tytuł: " . $row['page_title'] . 
                  " | <a href='?akcja=edytuj&id=" . $row['id'] . "'>Edytuj</a> | " .
                  "<a href='?akcja=usun&id=" . $row['id'] . "'>Usuń</a></li>";
    }
    $wynik .= "</ul>";
    $wynik .= "<br><a href='?akcja=dodaj'>Dodaj nową podstronę</a>";
    return $wynik;
}

function EdytujPodstrone($conn, $id) {
    $id = intval($id); // Zabezpieczenie przed SQL Injection

    // Pobranie danych podstrony
    $stmt = $conn->prepare("SELECT * FROM page_list WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $podstrona = $result->fetch_assoc();

    if (!$podstrona) {
        return "<p>Nie znaleziono podstrony o ID: " . $id . "</p>";
    }

    // Obsługa formularza
    if (isset($_POST['edytuj_podstrone'])) {
        $tytul = htmlspecialchars($_POST['tytul']);
        $tresc = $_POST['page_content'];
        $alias = htmlspecialchars($_POST['alias']);
        $status = isset($_POST['status']) ? 1 : 0;

        // Zabezpieczenie przed SQL Injection
        $stmt = $conn->prepare("UPDATE page_list SET page_title = ?, page_content = ?, alias = ?, status = ? WHERE id = ? LIMIT 1");
        $stmt->bind_param("ssssi", $tytul, $tresc, $alias, $status, $id);

        if ($stmt->execute()) {
            return "<p>Podstrona zaktualizowana!</p>";
        } else {
            return "<p>Błąd aktualizacji podstrony: " . $conn->error . "</p>";
        }
    }

    // Formularz edycji
    $wynik = "
    <h2>Edytuj podstronę</h2>
    <form method='post'>
        <label for='tytul'>Tytuł:</label><br>
        <input type='text' name='tytul' id='tytul' value='" . htmlspecialchars($podstrona['page_title']) . "'><br><br>

        <label for='page_content'>Treść:</label><br>
        <textarea name='page_content' id='page_content' rows='10' cols='50'>" . htmlspecialchars($podstrona['page_content']) . "</textarea><br><br>

        <label for='alias'>Alias:</label><br>
        <input type='text' name='alias' id='alias' value='" . htmlspecialchars($podstrona['alias']) . "'><br><br>

        <input type='checkbox' name='status' id='status' value='1' " . ($podstrona['status'] ? 'checked' : '') . ">
        <label for='status'>Aktywna</label><br><br>

        <input type='submit' name='edytuj_podstrone' value='Zapisz zmiany'>
    </form>";

    return $wynik;
}

function DodajNowaPodstrone($conn) {
    if (isset($_POST['dodaj_podstrone'])) {
        $tytul = htmlspecialchars($_POST['page_title']);
        $tresc = $_POST['page_content'];
        $alias = htmlspecialchars($_POST['alias']);
        $status = isset($_POST['status']) ? 1 : 0;

        // Wstawianie nowej podstrony do bazy
        $stmt = $conn->prepare("INSERT INTO page_list (page_title, page_content, alias, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $tytul, $tresc, $alias, $status);

        if ($stmt->execute()) {
            return "<p>Podstrona dodana!</p>";
        } else {
            return "<p>Błąd dodawania podstrony: " . $conn->error . "</p>";
        }
    }

    // Formularz dodawania
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
    $stmt = $conn->prepare("DELETE FROM page_list WHERE id = ? LIMIT 1"); // LIMIT 1 dla bezpieczeństwa
    $stmt->bind_param("i", $id);
    if($stmt->execute()) {
        return "Usunięto podstronę o ID: " . $id;
    } else {
        return "Błąd usuwania podstrony: " . $conn->error;
    }
}

if (isset($_POST['x1_submit'])) {
    if ($_POST['login_email'] == $GLOBALS['login'] && $_POST['login_pass'] == $GLOBALS['pass']) {
        $_SESSION['zalogowany'] = true;
    } else {
        echo "<p style='color:red;'>Błędny login lub hasło!</p>";
        echo FormularzLogowania();
        exit();
    }
}

// Zabezpieczenie przed nieautoryzowanym dostępem
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
} else {
    echo ListaPodstron($conn); // Domyślna akcja
}

$conn->close();
