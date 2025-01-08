<?php
include_once 'cfg.php';
include_once 'helpers.php';

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


// Function to display the edit page form
function displayEditPageForm($conn, $id) {
    $page = fetchPage($conn, $id);
     if (!$page) {
            return "<p>Nie znaleziono podstrony o ID: " . $id . "</p>";
        }

    if (isset($_POST['edytuj_podstrone'])) {
        $tytul = sanitize($_POST['tytul'] ?? '');
        $tresc = $_POST['page_content'] ?? '';
        $alias = sanitize($_POST['alias'] ?? '');
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
        <input type='text' name='tytul' id='tytul' value='" . sanitize($page['page_title'] ?? '') . "'><br><br>

        <label for='page_content'>Treść:</label><br>
        <textarea name='page_content' id='page_content' rows='10' cols='50'>" . sanitize($page['page_content'] ?? '') . "</textarea><br><br>

        <label for='alias'>Alias:</label><br>
        <input type='text' name='alias' id='alias' value='" . sanitize($page['alias'] ?? '') . "'><br><br>

        <input type='checkbox' name='status' id='status' value='1' " . ($page['status'] ? 'checked' : '') . ">
        <label for='status'>Aktywna</label><br><br>

        <input type='submit' name='edytuj_podstrone' value='Zapisz zmiany'>
    </form>";

    return $wynik;
}
// Function to display the add new page form
function displayAddPageForm($conn) {
    if (isset($_POST['dodaj_podstrone'])) {
       $tytul = sanitize($_POST['page_title'] ?? '');
       $tresc = $_POST['page_content'] ?? '';
       $alias = sanitize($_POST['alias'] ?? '');
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
?>