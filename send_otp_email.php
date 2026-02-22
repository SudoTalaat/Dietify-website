<?php
/**
 * send_otp_email.php
 */
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'healthyfood247x@gmail.com');
define('SMTP_PASS', 'ejaxgtyehxxzyyhf');
define('SMTP_PORT', 587);
define('SMTP_FROM_EMAIL', 'you@gmail.com');
define('SMTP_FROM_NAME', 'healthyfood');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendOtpEmail(mysqli $conn, int $userId, string $toEmail, string $toName, string $purpose = '2fa'): bool
{
  $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  $otpHash = password_hash($otp, PASSWORD_BCRYPT);

  $del = $conn->prepare(
    "DELETE FROM email_otps
         WHERE user_id = ? AND purpose = ? AND used_at IS NULL"
  );
  $del->bind_param('is', $userId, $purpose);
  $del->execute();
  $del->close();

  $ins = $conn->prepare(
    "INSERT INTO email_otps (user_id, otp_hash, purpose, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
  );
  $ins->bind_param('iss', $userId, $otpHash, $purpose);
  if (!$ins->execute()) {
    throw new Exception("DB error storing OTP: " . $ins->error);
  }
  $ins->close();

  $mail = new PHPMailer(true);
  $mail->isSMTP();
  $mail->Host = SMTP_HOST;
  $mail->SMTPAuth = true;
  $mail->Username = SMTP_USER;
  $mail->Password = SMTP_PASS;
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port = SMTP_PORT;

  $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
  $mail->addAddress($toEmail, $toName);
  $mail->isHTML(true);

  // Customize content based on purpose
  $subject = 'Your Login Verification Code';
  $title = '🔐 Verification Code';
  $leadText = 'Your one-time login code is:';

  if ($purpose === 'password_reset') {
    $subject = 'Password Reset Verification Code';
    $title = '🔄 Password Reset';
    $leadText = 'Your password reset code is:';
  }

  $mail->Subject = $subject;
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
?>