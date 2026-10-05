<?php
// 1. Laad alle gedownloade Composer tools (PHPMailer & DotEnv)
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Zorg dat PHP de gegevens uit het .env bestand kan lezen
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$statusMessage = "";

// 3. Controleren of de gebruiker op de verzendknop heeft gedrukt
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gegevens veilig ophalen uit het formulier
    $naam = htmlspecialchars($_POST['naam']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $klacht = htmlspecialchars($_POST['omschrijving']);

    // Check of alles is ingevuld
    if (!empty($naam) && !empty($email) && !empty($klacht)) {
        
        // Maak een nieuwe PHPMailer instantie aan
        $mail = new PHPMailer(true);

        try {
            // SMTP Server instellingen (Laden we RECHTSSTREEKS uit de .env!)
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];
            $mail->Password   = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['SMTP_PORT'];

            // Wie stuurt de mail en naar wie gaat hij?
            $mail->setFrom($_ENV['MY_EMAIL'], 'Klachtenverwerking CLG');
            $mail->addAddress($email, $naam);            // Mail naar de invuller van het formulier
            $mail->addCC($_ENV['MY_EMAIL']);             // Jezelf in de CC zetten zoals gevraagd!

            // De inhoud van de e-mail opbouwen
            $mail->isHTML(false); // Gewone tekst is prima voor deze opdracht
            $mail->Subject = 'Uw klacht is in behandeling';
            $mail->Body    = "Beste $naam,\n\nWe hebben je klacht in behandeling genomen. Hier zijn de details van je inzending:\n\n" .
                             "Naam: $naam\n" .
                             "E-mail: $email\n" .
                             "Omschrijving van de klacht:\n$klacht\n\nMet vriendelijke groet,\nDe Klachtencommissie";

            // Verstuur de mail!
            $mail->send();
            $statusMessage = "<p style='color: green; font-weight: bold;'>De klacht is succesvol ingediend! Controleer je Mailtrap inbox.</p>";
        } catch (Exception $e) {
            $statusMessage = "<p style='color: red;'>Er ging iets mis met verzenden: {$mail->ErrorInfo}</p>";
        }
    } else {
        $statusMessage = "<p style='color: red;'>Vul alstublieft alle velden in.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Klacht indienen</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f9f9f9; }
        form { max-width: 450px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px; box-sizing: border-box; }
        input[type="submit"] { background-color: #007BFF; color: white; border: none; cursor: pointer; font-weight: bold; }
        input[type="submit"]:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <h1>Klachtenformulier</h1>
    
    <!-- Hier tonen we of het verzenden gelukt of mislukt is -->
    <?php echo $statusMessage; ?>
    
    <form action="index.php" method="POST">
        <label for="naam">Je naam:</label>
        <input type="text" id="naam" name="naam" required>

        <label for="email">Je e-mailadres:</label>
        <input type="email" id="email" name="email" required>

        <label for="omschrijving">Omschrijving van de klacht:</label>
        <textarea id="omschrijving" name="omschrijving" rows="5" required></textarea>

        <input type="submit" value="Klacht indienen">
    </form>

</body>
</html>
