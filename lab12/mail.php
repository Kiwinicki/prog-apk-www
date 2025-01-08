<?php
include_once 'helpers.php';
function PokazKontakt()
{
    echo <<<HTML
<form method="POST" action="" class="signup-form">
    <div class="form-fields">
        <label for="email" class="visually-hidden">Email:</label>
        <input type="email" name="email" id="email" required class="form-input">
        <label for="temat" class="visually-hidden">Temat:</label>
        <input type="text" name="temat" id="temat" required class="form-input">
        <label for="tresc" class="visually-hidden">Treść wiadomości:</label>
        <textarea name="tresc" id="tresc" required class="form-input"></textarea>
        <button type="submit" name="wyslij" class="submit-button">Wyślij</button>
    </div>
</form>
HTML;
}

function WyslijMailKontakt($odbiorca)
{
    if (isset($_POST['wyslij'])) {
        $temat = $_POST['temat'];
        $tresc = $_POST['tresc'];
        $email = $_POST['email'];

        if (empty($temat) || empty($tresc) || empty($email)) {
            echo '[nie_wypelniles_pola]';
            return;
        }

        $mail['subject'] = $temat;
        $mail['body'] = $tresc;
        $mail['sender'] = $email;
        $mail['reciptient'] = $odbiorca;

        $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
        $header .= "MIME-Version: 1.0\n";
        $header .= "Content-Type: text/plain; charset=utf-8\n";
        $header .= "Content-Transfer-Encoding: 8bit\n";
        $header .= "X-Sender: <" . $mail['sender'] . ">\n";
        $header .= "X-Mailer: PHP/" . phpversion() . "\n";
        $header .= "X-Priority: 3\n";
        $header .= "Return-Path: <" . $mail['sender'] . ">\n";

        mail($mail['reciptient'], $mail['subject'], $mail['body'], $header);

        echo '[wiadomosc_wyslana]';
    }
}

function PrzypomnijHaslo($adminEmail)
{
    $haslo = 'noweHaslo123';

    $temat = "Przypomnienie hasła";
    $tresc = "Twoje hasło do panelu admina to: " . $haslo;
    $email = "admin@twojadomena.com";

    $header = "From: Przypomnienie hasła <" . $email . ">\n";
    $header .= "MIME-Version: 1.0\n";
    $header .= "Content-Type: text/plain; charset=utf-8\n";
    $header .= "Content-Transfer-Encoding: 8bit\n";
    $header .= "X-Sender: <" . $email . ">\n";
    $header .= "X-Mailer: PHP/" . phpversion() . "\n";
    $header .= "X-Priority: 3\n";
    $header .= "Return-Path: <" . $email . ">\n";

    mail($adminEmail, $temat, $tresc, $header);

    echo '[haslo_przypomniane]';
}
?>