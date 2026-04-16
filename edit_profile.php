<?php
require_once __DIR__ . '/init.php';

// Auth guard
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];
$message = '';
$msgType = 'success';

// ── Handle POST submission ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = !empty($_POST['age']) ? (int) $_POST['age'] : null;
    $weight = !empty($_POST['weight']) ? (float) $_POST['weight'] : null;
    $height = !empty($_POST['height']) ? (float) $_POST['height'] : null;
    $gender = $_POST['gender'] ?? '';

    // Handle avatar upload
    $avatarPath = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['avatar']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExt, $allowedExts)) {
            $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExt;
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                $avatarPath = $destination;
            } else {
                $message = "Failed to upload avatar image.";
                $msgType = 'error';
            }
        } else {
            $message = "Invalid image format. Allowed: JPG, PNG, GIF, WEBP.";
            $msgType = 'error';
        }
    }

    if (empty($message)) {
        if ($avatarPath) {
            $updateStmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, age=?, weight=?, height=?, gender=?, avatar=? WHERE id=?");
            $updateStmt->bind_param('sssiddssi', $username, $email, $phone, $age, $weight, $height, $gender, $avatarPath, $userId);
        } else {
            $updateStmt = $conn->prepare("UPDATE users SET username=?, email=?, phone=?, age=?, weight=?, height=?, gender=? WHERE id=?");
            $updateStmt->bind_param('sssiddsi', $username, $email, $phone, $age, $weight, $height, $gender, $userId);
        }

        if ($updateStmt->execute()) {
            $message = "Profile updated successfully!";
            $msgType = 'success';
        } else {
            $message = "Error updating profile. Email or username might already exist.";
            $msgType = 'error';
        }
        $updateStmt->close();
    }
}

// ── Load current user details ────────────────────────────────────────────────
$stmt = $conn->prepare("SELECT id, username, email, avatar, age, weight, height, gender, phone FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

include __DIR__ . '/header.php';
?>

<style>
    .edit-container {
        max-width: 600px;
        margin: 40px auto;
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .edit-container h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #555;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #ff6b35;
    }

    .row {
        display: flex;
        gap: 20px;
    }

    .col {
        flex: 1;
    }

    .btn-submit {
        display: block;
        width: 100%;
        padding: 15px;
        background: #ff6b35;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 30px;
    }

    .btn-submit:hover {
        background: #e55a2b;
    }

    .btn-cancel {
        display: block;
        width: 100%;
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        color: #555;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: bold;
        text-decoration: none;
        margin-top: 15px;
        transition: background 0.3s;
    }

    .btn-cancel:hover {
        background: #e9ecef;
    }

    .avatar-preview {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin: 0 auto 20px;
        display: block;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background: #eee;
    }
</style>

<div style="background: #f0f2f5; min-height: calc(100vh - 70px); padding: 40px 0;">
    <div class="edit-container">
        <h2>Edit Profile</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msgType; ?>"
                style="padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; background: <?php echo $msgType === 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $msgType === 'success' ? '#155724' : '#721c24'; ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <?php if (!empty($user['avatar']) && $user['avatar'] !== 'assets/images/default_avatar.png'): ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar Preview" class="avatar-preview">
            <?php else: ?>
                <div class="avatar-preview"
                    style="display:flex; align-items:center; justify-content:center; font-size:3rem; color:white; background:linear-gradient(135deg, #ff6b35, #ff9f1c);">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
            <?php endif; ?>

            <div class="form-group" style="text-align: center;">
                <label for="avatar" style="cursor: pointer; color: #ff6b35; text-decoration: underline;">Change Avatar
                    Picture</label>
                <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control"
                    value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control"
                    value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control"
                    value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>

            <div class="row">
                <div class="col form-group">
                    <label for="age">Age</label>
                    <input type="number" name="age" id="age" class="form-control"
                        value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" min="1" max="150">
                </div>
                <div class="col form-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($user['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($user['gender'] === 'female') ? 'selected' : ''; ?>>Female
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" step="0.1" name="weight" id="weight" class="form-control"
                        value="<?php echo htmlspecialchars($user['weight'] ?? ''); ?>">
                </div>
                <div class="col form-group">
                    <label for="height">Height (cm)</label>
                    <input type="number" step="0.1" name="height" id="height" class="form-control"
                        value="<?php echo htmlspecialchars($user['height'] ?? ''); ?>">
                </div>
            </div>

            <button type="submit" class="btn-submit">Save Changes</button>
            <a href="profile.php" class="btn-cancel">Back to Profile</a>
        </form>
    </div>
</div>

<script>
    // Preview image on select
    document.getElementById('avatar').addEventListener('change', function (e) {
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                const previewImg = document.querySelector('.avatar-preview');
                if (previewImg.tagName === 'IMG') {
                    previewImg.src = e.target.result;
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.className = 'avatar-preview';
                    previewImg.parentNode.replaceChild(newImg, previewImg);
                }
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>

</main>
</body>

</html>