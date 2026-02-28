<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Email Configuration from Environment becuse i fear i might get ban or something pls setup your gmail for it to work
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.example.com');
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');
define('SMTP_PORT', (int) ($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM'] ?? 'no-reply@example.com');
define('SMTP_FROM_NAME', $_ENV['SMTP_NAME'] ?? 'App Name');

/**
 * Internal helper to initialize a configured PHPMailer instance.
 * that did save about 30 lines of code
 * so i don't repate the same code in every function what will change is just the content of the email
 */
function initMailer(string $toEmail, string $toName, string $subject): PHPMailer
{
  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = SMTP_HOST;
  $mail->SMTPAuth = true;
  $mail->Username = SMTP_USER;
  $mail->Password = SMTP_PASS;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = SMTP_PORT;
  $mail->CharSet = 'UTF-8';

  $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
  $mail->addAddress($toEmail, $toName);
  $mail->isHTML(true);
  $mail->Subject = $subject;

  return $mail;
}

function sendOtpEmail(mysqli $conn, int $userId, string $toEmail, string $toName, string $purpose = '2fa'): bool
{
  $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  $otpHash = password_hash($otp, PASSWORD_BCRYPT);

  $del = $conn->prepare("DELETE FROM email_otps WHERE user_id = ? AND purpose = ? AND used_at IS NULL");
  $del->bind_param('is', $userId, $purpose);
  $del->execute();
  $del->close();

  $ins = $conn->prepare("INSERT INTO email_otps (user_id, otp_hash, purpose, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(),
INTERVAL 10 MINUTE))");
  $ins->bind_param('iss', $userId, $otpHash, $purpose);
  if (!$ins->execute()) {
    //
    throw new Exception("DB error storing OTP: " . $ins->error);
  }
  //THAT should show in the logs
  $ins->close();

  // Customize content based on purpose
  $subject = 'Your Login Verification Code';
  $title = '🔐 Verification Code';
  $leadText = 'Your one-time login code is:';

  if ($purpose === 'password_reset') {
    $subject = 'Password Reset Verification Code';
    $title = '🔄 Password Reset';
    $leadText = 'Your password reset code is:';
  }

  $mail = initMailer($toEmail, $toName, $subject);

  $mail->Body = "
<div style='font-family:Inter,sans-serif;max-width:420px;margin:auto;
                     background:#0f172a;color:#e2e8f0;border-radius:12px;overflow:hidden'>
  <div style='background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:28px 32px'>
    <h2 style='margin:0;font-size:1.4rem'>$title</h2>
  </div>
  <div style='padding:28px 32px'>
    <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
    <p>$leadText</p>
    <div style='font-size:2.4rem;font-weight:700;letter-spacing:10px;
                        text-align:center;background:#1e293b;border-radius:8px;
                        padding:18px;margin:20px 0;color:#818cf8'>$otp</div>
    <p style='font-size:.85rem;color:#94a3b8'>
      This code expires in <strong>10 minutes</strong>.<br>
      If you did not request this, please ignore this email.
    </p>
  </div>
</div>";
  $mail->AltBody = "$subject: $otp (expires in 10 minutes)";

  return $mail->send();
}

function sendSecurityAlertEmail(string $toEmail, string $toName): bool
{
  $mail = initMailer($toEmail, $toName, 'Security Alert: Multiple Failed Login Attempts');

  $mail->Body = "
<div style='font-family:Inter,sans-serif;max-width:420px;margin:auto;
                     background:#450a0a;color:#fecaca;border-radius:12px;overflow:hidden;border:1px solid #7f1d1d'>
  <div style='background:#7f1d1d;padding:28px 32px;text-align:center'>
    <h2 style='margin:0;font-size:1.4rem'>⚠️ Security Alert</h2>
  </div>
  <div style='padding:28px 32px'>
    <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
    <p>We noticed over <strong>25 failed login attempts</strong> for your account in the last hour.</p>
    <div style='background:#991b1b;border-radius:8px;padding:15px;margin:20px 0;text-align:center'>
      If this wasn't you, your account might be under a brute-force attack.
    </div>
    <p style='font-size:.85rem;color:#fca5a5'>
      Your account is still safe, but we recommend ensuring you have 2FA enabled and using a strong, unique password.
    </p>
  </div>
</div>";
  $mail->AltBody = "Security Alert: We noticed over 25 failed login attempts for your account in the last hour.";

  return $mail->send();
}

function sendBackupCodesEmail(string $toEmail, string $toName, array $codes): bool
{
  $mail = initMailer($toEmail, $toName, 'Your Backup Codes for 2FA TOTP');

  // Build list safely
  $codesList = '';
  foreach ($codes as $code) {
    $codesList .= '<li>' . htmlspecialchars($code) . '</li>';
  }

  $mail->Body = "
<p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
<p>Here are your backup recovery codes for <strong>2FA TOTP</strong>:</p>
<ul>
  $codesList
</ul>
<p><strong>Each code can only be used once.</strong></p>
";

  $mail->AltBody = "Your backup codes: " . implode(', ', $codes);

  return $mail->send();
}
?>