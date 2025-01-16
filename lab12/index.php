<?php
// Enable error reporting for debugging (remove for production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'utils.php'; // Include utils.php for database connection and functions
include_once 'mail.php';

// Function to fetch a page based on alias or ID
function fetchPage($alias)
{
    global $db; // Use the PDO connection from utils.php
    $stmt = $db->prepare("SELECT * FROM page_list WHERE alias = :alias OR (alias IS NULL AND id = :id) LIMIT 1");
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':id', $alias);
    $stmt->execute();
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    return $page ? $page : null;
}

// Function to fetch all pages
function fetchAllPages()
{
    global $db; // Use the PDO connection from utils.php
    $stmt = $db->query("SELECT id, page_title, alias FROM page_list WHERE status = 1 ORDER BY page_title ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch page content
$page_alias = isset($_GET['alias']) ? sanitizeInput($_GET['alias']) : 'glowna';
$page = fetchPage($page_alias);

// Fetch all pages for the sidebar
$pages = fetchAllPages();
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

<body class="container">
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
                <li><a href="./shop/index.php">Sklep</a></li>
            </ul>
        </nav>
    </header>
    <div class="main-content">
        <!-- Sidebar -->
        <aside class="content">
            <h2 class="sidebar-title">Strony</h2>
            <ul class="pages-list">
                <?php foreach ($pages as $page_item): ?>
                    <li>
                        <a href="index.php?alias=<?php echo $page_item['alias']; ?>">
                            <?php echo $page_item['page_title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <!-- Main Content -->
        <main class="content">
            <?php
            if ($page) {
                echo $page['page_content'];
            } else {
                echo "<p>Strona nie istnieje</p>";
            }
            ?>
            <?php showContact(); ?>
        </main>
    </div>
    <footer class="main-footer">
        <?php
        $nrIndeksu = 169257;
        $nrGrupy = 2;
        echo "Autor: Dawid Koterwas " . $nrIndeksu . " grupa " . $nrGrupy . "<br/><br/>";
        ?>
    </footer>
</body>

</html>