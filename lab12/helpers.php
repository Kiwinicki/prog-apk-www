<?php
// Function to sanitize user input
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''));
}

// Helper function to fetch product details from database
function fetchProductById($conn, $id)
{
  $stmt = $conn->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result === false || $result->num_rows === 0) {
    $stmt->close();
    return null;
  }
  $produkt = $result->fetch_assoc();
  $stmt->close();
  return $produkt;
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
// Function to fetch a single category by id
function fetchCategoryById($conn, $id){
  $stmt = $conn->prepare("SELECT * FROM kategorie WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result === false || $result->num_rows === 0) {
      $stmt->close();
      return null;
   }
  $kategoria = $result->fetch_assoc();
  $stmt->close();
  return $kategoria;
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
// Function to fetch categories for a dropdown list
function fetchCategoriesForDropdown($conn) {
  $query = "SELECT id, nazwa FROM kategorie LIMIT 100";
  $result = $conn->query($query);
  if (!$result) {
      return null; // Return null if the query fails
  }
  $kategorie = "";
  while ($row = $result->fetch_assoc()) {
      $kategorie .= "<option value='" . $row['id'] . "'>" . sanitize($row['nazwa'] ?? '') . "</option>";
  }
   return $kategorie;
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
        $wynik .= "<li>" . str_repeat("    ", $level) .
        "ID: " . $row['id'] . " | " .
        "Nazwa: " . sanitize($row['nazwa'] ?? '') . " | " .
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
        $wynik .= "<li>" . str_repeat("    ", $level) . sanitize($row['nazwa']);
        $wynik .= generateCategoryTreeFront($conn, $row['id'], $level + 1);
        $wynik .= "</li>";
    }
    $wynik .= "</ul>";
    $stmt->close();
    return $wynik;
}
?>