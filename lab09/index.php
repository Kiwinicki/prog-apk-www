<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

include_once 'cfg.php';
include_once 'mail.php';

$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function showPage($conn, $alias)
{
    $stmt = $conn->prepare("SELECT * FROM page_list WHERE alias = ? OR (alias IS NULL AND id = ?) LIMIT 1"); // Szukamy po aliasie lub po ID, jeśli alias jest pusty
    $stmt->bind_param("si", $alias, $alias); // Alias może być stringiem lub liczbą (ID)
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $row = $result->fetch_assoc();
    if (empty($row['alias'])) {
        return '[page_not_found]';
    } else {
        return $row['page_content'];
    }
}

$page_alias = '';
if (isset($_GET['alias'])) {
    $page_alias = htmlspecialchars($_GET['alias']);
} else {
    $page_alias = 'glowna';
}

$page_content = showPage($conn, $page_alias);

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
        if ($page_content !== '[page_not_found]') {
            echo $page_content;
        } else {
            echo "<p>Strona nie istnieje</p>";
        }
        ?>
        <?php PokazKontakt() ?>
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