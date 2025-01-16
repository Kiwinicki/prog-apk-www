<?php
/**
 * Displays the contact form.
 */
function showContact()
{
    echo <<<HTML
<form method="POST" action="" class="signup-form">
    <div class="form-fields">
        <label for="email" class="visually-hidden">Email:</label>
        <input type="email" name="email" id="email" required class="form-input">
        <label for="subject" class="visually-hidden">Temat:</label>
        <input type="text" name="subject" id="subject" required class="form-input">
        <label for="content" class="visually-hidden">Zawartość:</label>
        <textarea name="content" id="content" required class="form-input"></textarea>
        <button type="submit" name="wyslij" class="submit-button">Wyślij</button>
    </div>
</form>
HTML;
}

/**
 * Sends a contact form email.
 *
 * @param string $recipient The email address to send the message to.
 */
function sendContactEmail($recipient)
{
    if (isset($_POST['wyslij'])) { // If the "send" button was clicked
        $subject = $_POST['subject']; // Get the subject from the form
        $messageBody = $_POST['content']; // Get the message from the form
        $senderEmail = $_POST['email']; // Get the sender's email from the form

        // Check if any of the required fields are empty
        if (empty($subject) || empty($messageBody) || empty($senderEmail)) {
            echo '[not_all_fields_filled]'; // Output a message indicating fields are missing
            return; // Stop execution of the function
        }

        $mail['subject'] = $subject; // Set the subject for the email
        $mail['body'] = $messageBody; // Set the message body for the email
        $mail['sender'] = $senderEmail; // Set the sender's email for the email
        $mail['recipient'] = $recipient; // Set the recipient email

        // Construct the email headers
        $header = "From: Contact Form <" . $mail['sender'] . ">\n"; // Set the "From" header
        $header .= "MIME-Version: 1.0\n"; // Set the MIME version
        $header .= "Content-Type: text/plain; charset=utf-8\n"; // Set the content type and charset
        $header .= "Content-Transfer-Encoding: 8bit\n"; // Set the content transfer encoding
        $header .= "X-Sender: <" . $mail['sender'] . ">\n"; // Set the X-Sender header
        $header .= "X-Mailer: PHP/" . phpversion() . "\n"; // Set the X-Mailer header
        $header .= "X-Priority: 3\n"; // Set the priority
        $header .= "Return-Path: <" . $mail['sender'] . ">\n"; // Set the return path

        // Attempt to send the email
        mail($mail['recipient'], $mail['subject'], $mail['body'], $header);

        echo '[message_sent]'; // Output a success message
    }
}
?>