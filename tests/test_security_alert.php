<?php
// We need to load vendor/autoload.php manually here because we want to see variables before calling functions
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo "--- ENV CONFIG CHECK ---\n";
echo "SMTP_HOST: " . ($_ENV['SMTP_HOST'] ?? 'NOT SET') . "\n";
echo "SMTP_PORT: " . ($_ENV['SMTP_PORT'] ?? 'NOT SET') . "\n";
echo "SMTP_USER: " . ($_ENV['SMTP_USER'] ?? 'NOT SET') . "\n";
echo "SMTP_PASS: " . (isset($_ENV['SMTP_PASS']) ? '********' : 'NOT SET') . "\n";
echo "------------------------\n\n";

require_once __DIR__ . '/includes/send_otp_email.php';

$toEmail = $_ENV['SMTP_USER'] ?? 'test@example.com';
$toName = 'Test User';

echo "Attempting to send a DEBUG Security Alert Email to $toEmail...\n";

// We'll temporarily modify initMailer in our mind or just override it here for testing
function testSendWithDebug($toEmail, $toName)
{
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) $_ENV['SMTP_PORT'];
        $mail->CharSet = 'UTF-8';

        // ENABLE DEBUGGING
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = 'echo';

        $mail->setFrom($_ENV['SMTP_FROM'] ?? $_ENV['SMTP_USER'], $_ENV['SMTP_NAME'] ?? 'Test App');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = 'DEBUG Security Alert';
        $mail->Body = 'This is a test security alert with full debug output enabled.';

        echo "--- START SMTP LOG ---\n";
        $sent = $mail->send();
        echo "\n--- END SMTP LOG ---\n";

        return $sent;
    } catch (Exception $e) {
        echo "\n--- SMTP ERROR ---\n";
        echo $e->getMessage() . "\n";
        return false;
    }
}

$success = testSendWithDebug($toEmail, $toName);

if ($success) {
    echo "\nRESULT: SUCCESS! The email was accepted by the SMTP server.\n";
} else {
    echo "\nRESULT: FAILED. Please review the SMTP log above.\n";
}
