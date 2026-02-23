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

function sendSecurityAlertEmail(string $toEmail, string $toName): bool
{
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
  $mail->Subject = 'Security Alert: Multiple Failed Login Attempts';

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

/**
 * Sends a list of 2FA backup codes to the user via email.
 */
function sendBackupCodesEmail(string $toEmail, string $toName, array $codes): bool
{
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
  $mail->Subject = 'Your 2FA Backup Recovery Codes';

  $codesList = '';
  foreach ($codes as $code) {
    $codesList .= "<li style='font-family:monospace; font-size: 1.1rem; padding: 5px 0;'>$code</li>";
  }

  $mail->Body = "
    <div style='font-family:Inter,sans-serif;max-width:420px;margin:auto; background:#ffffff;color:#444;border-radius:12px;overflow:hidden;border:1px solid #e1e4e8'>
      <div style='background:#f8f9fa;padding:28px 32px;text-align:center;border-bottom:1px solid #e1e4e8'>
        <h2 style='margin:0;color:#24292e'>🔒 Backup Recovery Codes</h2>
      </div>
      <div style='padding:28px 32px'>
        <p>Hi <strong>" . htmlspecialchars($toName) . "</strong>,</p>
        <p>You have successfully enabled 2FA with Authenticator App. Here are your <strong>10 one-time recovery codes</strong>. Store them in a safe place.</p>
        
        <ul style='list-style:none; padding:0; background:#f6f8fa; border-radius:8px; padding:20px; border:1px solid #d1d5da'>
            $codesList
        </ul>

        <div style='background:#fffbdd;border:1px solid #d4a017;border-radius:8px;padding:15px;margin:20px 0; font-size:0.85rem; color:#735c0f'>
            ⚠️ <strong>Warning:</strong> Each code can only be used <strong>once</strong>. If you lose your TOTP device, use these to regain access.
        </div>
      </div>
    </div>";

  $mail->AltBody = "Your 2FA Backup Codes are: " . implode(", ", $codes);

  return $mail->send();
}
?>