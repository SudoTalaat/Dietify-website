<?php

## UNUSED FILE

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/send_otp_email.php';

// Test configuration
$testUserId = 2; // Based on previous DB check, user 2 has email/activity
$testEmail = 'test@example.com';
$testUsername = 'TestUser';

echo "--- 2FA LOGIC VERIFICATION ---\n";

// 1. Clear old unused OTPs for this user to have a clean state
$conn->query("DELETE FROM email_otps WHERE user_id = $testUserId AND purpose = 'twofa'");

echo "1. Generating and storing OTP...\n";
try {
    // This will use the new 'twofa' purpose by default
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otpHash = password_hash($otp, PASSWORD_BCRYPT);

    $ins = $conn->prepare("INSERT INTO email_otps (user_id, otp_hash, purpose, expires_at) VALUES (?, ?, 'twofa', DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
    $ins->bind_param('is', $testUserId, $otpHash);
    $ins->execute();
    $ins->close();

    echo "SUCCESS: OTP generated ($otp) and stored with purpose 'twofa'.\n";

    echo "2. Verifying the OTP...\n";
    // Simulate verify_2fa.php logic
    $otpStmt = $conn->prepare(
        "SELECT id, otp_hash FROM email_otps
         WHERE user_id = ?
           AND purpose  = 'twofa'
           AND used_at  IS NULL
           AND expires_at > NOW()
         ORDER BY created_at DESC
         LIMIT 1"
    );
    $otpStmt->bind_param('i', $testUserId);
    $otpStmt->execute();
    $otpRow = $otpStmt->get_result()->fetch_assoc();
    $otpStmt->close();

    if ($otpRow && password_verify($otp, $otpRow['otp_hash'])) {
        echo "SUCCESS: OTP verification passed!\n";
    } else {
        echo "FAILED: OTP verification failed. Row found: " . ($otpRow ? 'Yes' : 'No') . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "------------------------------\n";
