<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
if($_GET['idp'] == '') $strona = '/html/glowna.html';
if($_GET['idp'] == 'dl') $strona = '/html/dl.html';
if($_GET['idp'] == 'nlp') $strona = '/html/nlp.html';
if($_GET['idp'] == 'cv') $strona = '/html/cv.html';
if($_GET['idp'] == 'rl') $strona = '/html/rl.html';
if($_GET['idp'] == 'etyka') $strona = '/html/etyka.html';
if($_GET['idp'] == 'filmy') $strona = '/html/filmy.html';

if (!file_exists(__DIR__ . $strona)) {
    $strona = null;
    $errorMessage = "Strona nie istnieje";
}

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
        <h1 class="site-title"><a href="index.html">Machine Learning</a></h1>
        <nav class="main-nav">
            <ul class="menu">
                <li><a href="index.php?idp=dl">Deep Learning</a></li>
                <li><a href="index.php?idp=nlp">NLP</a></li>
                <li><a href="index.php?idp=cv">Computer Vision</a></li>
                <li><a href="index.php?idp=rl">Reinforcement Learning</a></li>
                <li><a href="index.php?idp=etyka">Etyka AI</a></li>
                <li><a href="index.php?idp=filmy">Filmy</a></li>
            </ul>
        </nav>
    </header>
    <main class="content">
    <?php 
    if ($strona) {
        include(__DIR__ . $strona);
    } else {
        echo "<p>$errorMessage</p>";
    }
    ?>
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