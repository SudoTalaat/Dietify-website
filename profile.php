<?php
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

session_start();
require 'db_connect.php';

// Auth guard
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];
$message = '';
$msgType = 'success';

// ── Load user details ────────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "SELECT u.id, u.username, u.email, u.twofa_method, u.created_at, t.totp_secret, t.confirmed_at as totp_confirmed_at
     FROM users u
     LEFT JOIN user_totp t ON u.id = t.user_id
     WHERE u.id = ?"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// ── Handle POST actions (2FA Management) ─────────────────────────────────────
$action = $_POST['action'] ?? '';

// 1. DISABLE 2FA
if ($action === 'disable') {
    $conn->begin_transaction();
    try {
        $upd = $conn->prepare("UPDATE users SET twofa_method='none' WHERE id=?");
        $upd->bind_param('i', $userId);
        $upd->execute();
        $upd->close();

        $del = $conn->prepare("DELETE FROM user_totp WHERE user_id=?");
        $del->bind_param('i', $userId);
        $del->execute();
        $del->close();

        $conn->commit();
        $message = "Two-factor authentication has been disabled.";
        $msgType = 'error';
        $user['twofa_method'] = 'none';
        $user['totp_secret'] = null;
        $user['totp_confirmed_at'] = null;
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error disabling 2FA.";
        $msgType = 'error';
    }
}
// 2. ENABLE EMAIL 2FA
elseif ($action === 'enable_email') {
    $conn->begin_transaction();
    try {
        $upd = $conn->prepare("UPDATE users SET twofa_method='email' WHERE id=?");
        $upd->bind_param('i', $userId);
        $upd->execute();
        $upd->close();

        $del = $conn->prepare("DELETE FROM user_totp WHERE user_id=?");
        $del->bind_param('i', $userId);
        $del->execute();
        $del->close();

        $conn->commit();
        $message = "Email 2FA enabled successfully!";
        $user['twofa_method'] = 'email';
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error enabling email 2FA.";
        $msgType = 'error';
    }
}
// 3. START TOTP SETUP
elseif ($action === 'start_totp') {
    require_once 'vendor/autoload.php';
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $secret = $google2fa->generateSecretKey();

    $conn->begin_transaction();
    try {
        // Clear any existing (unconfirmed/confirmed) TOTP
        $del = $conn->prepare("DELETE FROM user_totp WHERE user_id=?");
        $del->bind_param('i', $userId);
        $del->execute();
        $del->close();

        // Insert new secret (not confirmed yet)
        // Note: The schema says confirmed_at is NOT NULL, so we might need to handle that.
        // Actually, looking at the schema: `confirmed_at` datetime NOT NULL.
        // This is a bit tricky if we want to store it before confirmation.
        // Let's use a dummy date or change our approach to only store on confirmation,
        // but the app flow generates the secret first.
        // I'll use '1970-01-01 00:00:00' as a dummy for "not confirmed".
        $dummyDate = '1970-01-01 00:00:00';
        $ins = $conn->prepare("INSERT INTO user_totp (user_id, totp_secret, confirmed_at) VALUES (?, ?, ?)");
        $ins->bind_param('iss', $userId, $secret, $dummyDate);
        $ins->execute();
        $ins->close();

        $conn->commit();
        $user['totp_secret'] = $secret;
        $user['totp_confirmed_at'] = null; // We'll treat the dummy as null in display
        $showQr = true;
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Error starting TOTP setup.";
        $msgType = 'error';
    }
}
// 4. CONFIRM TOTP
elseif ($action === 'confirm_totp') {
    require_once 'vendor/autoload.php';
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $code = trim($_POST['totp_code'] ?? '');

    $s2 = $conn->prepare("SELECT totp_secret FROM user_totp WHERE user_id=?");
    $s2->bind_param('i', $userId);
    $s2->execute();
    $secret = $s2->get_result()->fetch_assoc()['totp_secret'] ?? '';
    $s2->close();

    if ($google2fa->verifyKey($secret, $code)) {
        $now = date('Y-m-d H:i:s');
        $conn->begin_transaction();
        try {
            $upd1 = $conn->prepare("UPDATE users SET twofa_method='totp' WHERE id=?");
            $upd1->bind_param('i', $userId);
            $upd1->execute();
            $upd1->close();

            $upd2 = $conn->prepare("UPDATE user_totp SET confirmed_at=? WHERE user_id=?");
            $upd2->bind_param('si', $now, $userId);
            $upd2->execute();
            $upd2->close();

            // ── GENERATE BACKUP CODES ────────────────────────────────────────
            $plainCodes = [];
            for ($i = 0; $i < 10; $i++) {
                $code = strtoupper(bin2hex(random_bytes(4)));
                $plainCodes[] = $code;
                $hash = password_hash($code, PASSWORD_BCRYPT);
                $ins = $conn->prepare("INSERT INTO backup_codes (user_id, code_hash) VALUES (?, ?)");
                $ins->bind_param('is', $userId, $hash);
                $ins->execute();
                $ins->close();
            }

            require_once 'send_otp_email.php';
            sendBackupCodesEmail($user['email'], $user['username'], $plainCodes);

            $_SESSION['new_backup_codes'] = $plainCodes;
            // ───────────────────────────────────────────────────────────────

            $conn->commit();
            $message = "Authenticator app enabled! Backup codes sent to email. 🎉";
            $user['twofa_method'] = 'totp';
            $user['totp_confirmed_at'] = $now;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error confirming TOTP: " . $e->getMessage();
            $msgType = 'error';
        }
    } else {
        $message = "Invalid code – please try again.";
        $msgType = 'error';
        $showQr = true;
        $user['totp_secret'] = $secret;
    }
}

// ── Build TOTP QR code if required ───────────────────────────────────────────
$qrBase64 = '';
if (!empty($showQr) && !empty($user['totp_secret'])) {
    require_once 'vendor/autoload.php';
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $otpUrl = $google2fa->getQRCodeUrl('Healthy Food', $user['email'], $user['totp_secret']);
    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_MARKUP_SVG,
        'eccLevel' => QRCode::ECC_L,
        'scale' => 6,
    ]);
    $qrBase64 = (new QRCode($options))->render($otpUrl);
}

$currentMethod = $user['twofa_method'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – Healthy Food</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .profile-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 30px;
        }

        .user-card,
        .security-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            font-weight: 700;
        }

        .user-info {
            text-align: center;
        }

        .user-info h2 {
            margin: 10px 0 5px;
            color: #333;
        }

        .user-info p {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .info-grid {
            text-align: left;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-size: 0.8rem;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-weight: 600;
            color: #444;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #f8f9fa;
            color: #dc3545;
            border: 1px solid #eee;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #fee2e2;
            border-color: #fecaca;
        }

        /* Backup Codes UI */
        .backup-codes-box {
            background: #f8f9fa;
            border: 2px dashed #d1d5da;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            animation: slideIn 0.5s ease;
        }

        .codes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }

        .code-item {
            font-family: monospace;
            background: #fff;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e1e4e8;
            text-align: center;
            font-weight: 700;
            color: #24292e;
            font-size: 1.1rem;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body style="background: #f0f2f5;">

    <div class="profile-container">
        <!-- ── LEFT: USER INFO ── -->
        <div class="user-card">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <h2>
                    <?php echo htmlspecialchars($user['username']); ?>
                </h2>
                <p>Member since
                    <?php echo date('M Y', strtotime($user['created_at'])); ?>
                </p>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Email Address</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($user['email']); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Account Security</div>
                        <div class="info-value">
                            <?php if ($currentMethod === 'none'): ?>
                                <span style="color:#dc3545">● Standard</span>
                            <?php else: ?>
                                <span style="color:#28a745">● 2FA Protected</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <a href="login.php?logout=1" class="logout-btn">Sign Out</a>
            </div>
        </div>

        <!-- ── RIGHT: SECURITY & 2FA ── -->
        <div class="security-card">
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                🛡️ Security Settings
            </h3>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $msgType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Backup Codes Display (One-time) -->
            <?php if (!empty($_SESSION['new_backup_codes'])): ?>
                <div class="backup-codes-box">
                    <h4 style="margin: 0; color: #d4a017;">⚠️ Save your Backup Codes</h4>
                    <p style="font-size: 0.85rem; color: #666; margin: 5px 0 15px;">
                        Each code can be used once to log in if you lose your phone.
                    </p>
                    <div class="codes-grid">
                        <?php foreach ($_SESSION['new_backup_codes'] as $code): ?>
                            <div class="code-item"><?php echo htmlspecialchars($code); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php unset($_SESSION['new_backup_codes']); ?>
            <?php endif; ?>

            <div class="twofa-status">
                <?php if ($currentMethod === 'none'): ?>
                    <span class="badge badge-off">🔓 2FA is currently Disabled</span>
                <?php elseif ($currentMethod === 'email'): ?>
                    <span class="badge badge-on">✅ Email OTP Protected</span>
                <?php elseif ($currentMethod === 'totp'): ?>
                    <span class="badge badge-on">✅ Authenticator App Protected</span>
                <?php endif; ?>
            </div>

            <!-- QR Setup Step -->
            <?php if (!empty($showQr)): ?>
                <div class="qr-panel">
                    <p class="qr-instruction">Scan this with Google Authenticator:</p>
                    <div class="qr-container">
                        <img src="<?php echo $qrBase64; ?>" alt="QR Code">
                    </div>
                    <p class="qr-manual">Manual key: <code class="secret-code"><?php echo $user['totp_secret']; ?></code>
                    </p>
                    <form method="POST" class="confirm-form">
                        <input type="hidden" name="action" value="confirm_totp">
                        <label
                            style="display:block; margin-bottom: 8px; font-size: 0.9rem; color: #475569; font-weight: 600;">
                            Enter the 6-digit code:
                        </label>
                        <input type="text" name="totp_code" class="otp-input" maxlength="6" required autofocus
                            inputmode="numeric" pattern="[0-9]*">
                        <button type="submit" class="settings-btn btn-totp" style="margin-top:15px">Verify & Enable</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="settings-actions">
                <?php if ($currentMethod !== 'email'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="enable_email">
                        <button type="submit" class="settings-btn btn-email">
                            📧
                            <?php echo ($currentMethod === 'none') ? 'Enable' : 'Switch to'; ?> Email OTP
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($currentMethod !== 'totp' && empty($showQr)): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="start_totp">
                        <button type="submit" class="settings-btn btn-totp">
                            🔐
                            <?php echo ($currentMethod === 'none') ? 'Enable' : 'Switch to'; ?> Authenticator App
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($currentMethod !== 'none'): ?>
                    <form method="POST" onsubmit="return confirm('Disable 2FA? This makes your account less secure.')">
                        <input type="hidden" name="action" value="disable">
                        <button type="submit" class="settings-btn btn-disable">
                            🔓 Disable Two-Factor
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>