<?php

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_password = 'passwd123';
$db_name = 'lab12';

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Checks if the user is logged in.
 * @return bool True if the user is logged in, false otherwise.
 */
function isLoggedIn() {
    // Replace with actual authentication logic (session based)
    return isset($_SESSION['user_id']);
}


/**
 *  Redirects the user to the login page.
 */
function redirectToLogin() {
    header('Location: login.php');
    exit;
}

/**
 * Sanitizes input to prevent SQL injection.
 * @param string $input The input to sanitize.
 * @return string The sanitized input.
 */
function sanitizeInput($input) {
    global $db;
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Adds a new product to the database.
 * @param string $title The title of the product.
 * @param string $description The description of the product.
 * @param float $net_price The net price of the product.
 * @param float $vat The VAT rate for the product.
 * @param int $stock_quantity The stock quantity of the product.
 * @param int $status The status of the product (e.g., active/inactive).
 * @param int $category The category ID of the product.
 * @param string $dimensions The dimensions of the product.
 * @param string $image The image URL of the product.
 */
function addProduct($title, $description, $net_price, $vat, $stock_quantity, $status, $category, $dimensions, $image) {
    global $db;
    $stmt = $db->prepare("INSERT INTO products (title, description, net_price, vat, stock_quantity, status, category, dimensions, image) VALUES (:title, :description, :net_price, :vat, :stock_quantity, :status, :category, :dimensions, :image)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':net_price', $net_price);
    $stmt->bindParam(':vat', $vat);
    $stmt->bindParam(':stock_quantity', $stock_quantity);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':dimensions', $dimensions);
    $stmt->bindParam(':image', $image);
    $stmt->execute();
}

/**
 * Updates an existing product in the database.
 * @param int $id The ID of the product to update.
 * @param string $title The new title of the product.
 * @param string $description The new description of the product.
 * @param float $net_price The new net price of the product.
 * @param float $vat The new VAT rate for the product.
 * @param int $stock_quantity The new stock quantity of the product.
 * @param int $status The new status of the product.
 * @param int $category The new category ID of the product.
 * @param string $dimensions The new dimensions of the product.
 * @param string $image The new image URL of the product.
 */
function updateProduct($id, $title, $description, $net_price, $vat, $stock_quantity, $status, $category, $dimensions, $image) {
    global $db;
    $stmt = $db->prepare("UPDATE products SET title = :title, description = :description, net_price = :net_price, vat = :vat, stock_quantity = :stock_quantity, status = :status, category = :category, dimensions = :dimensions, image = :image WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':net_price', $net_price);
    $stmt->bindParam(':vat', $vat);
    $stmt->bindParam(':stock_quantity', $stock_quantity);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':dimensions', $dimensions);
    $stmt->bindParam(':image', $image);
    $stmt->execute();
}

/**
 * Removes a product from the database by its ID.
 * @param int $id The ID of the product to remove.
 */
function removeProduct($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

/**
 * Retrieves all products from the database.
 * @return array An array of products.
 */
function getAllProducts() {
    global $db;
    $stmt = $db->query("SELECT id, title, description, image FROM products");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retrieves a product by its ID.
 * @param int $id The ID of the product.
 * @return array|false The product data or false if not found.
 */
function getProductById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retrieves all categories from the database.
 * @return array An array of categories.
 */
function getAllCategories() {
    global $db;
    $stmt = $db->query("SELECT id, name, parent FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches all pages from the database.
 * @return array An array of pages.
 */
function getAllPages() {
    global $db;
    $stmt = $db->query("SELECT * FROM page_list");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetches a single page by its ID.
 * @param int $id The ID of the page.
 * @return array|false The page data or false if not found.
 */
function getPageById($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM page_list WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Adds a new page to the database.
 * @param string $title The title of the page.
 * @param string $content The content of the page.
 * @param string $alias The alias (URL slug) of the page.
 * @param int $status The status of the page (1 for active, 0 for inactive).
 */
function addPage($title, $content, $alias, $status) {
    global $db;
    if (strlen($alias) > 40) {
        throw new Exception("Alias must be 40 characters or less.");
    }
    $stmt = $db->prepare("INSERT INTO page_list (page_title, page_content, alias, status) VALUES (:title, :content, :alias, :status)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
}

/**
 * Updates an existing page in the database.
 * @param int $id The ID of the page to update.
 * @param string $title The new title of the page.
 * @param string $content The new content of the page.
 * @param string $alias The new alias (URL slug) of the page.
 * @param int $status The new status of the page (1 for active, 0 for inactive).
 */
function updatePage($id, $title, $content, $alias, $status) {
    global $db;
    if (strlen($alias) > 40) {
        throw new Exception("Alias must be 40 characters or less.");
    }
    $stmt = $db->prepare("UPDATE page_list SET page_title = :title, page_content = :content, alias = :alias, status = :status WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':status', $status);
    $stmt->execute();
}

/**
 * Deletes a page from the database by its ID.
 * @param int $id The ID of the page to delete.
 */
function deletePage($id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM page_list WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}