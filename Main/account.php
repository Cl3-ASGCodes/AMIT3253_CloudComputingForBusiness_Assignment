<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';
require_login();

$uid = current_user_id();
$profileError = '';
$profileSuccess = '';
$passwordError = '';
$passwordSuccess = '';

// Fetch profile data joined with login credentials
$user = db_fetch_one('
    SELECT 
        u.id, 
        u.name, 
        u.email, 
        u.id_number, 
        u.faculty, 
        u.date_of_birth, 
        u.login_id,
        l.username,
        l.password_hash
    FROM users u
    LEFT JOIN login l ON l.id = u.login_id
    WHERE u.id = ?
', [$uid]);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'profile') {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $faculty       = trim($_POST['faculty'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');

    if ($name === '' || $email === '' || $date_of_birth === '') {
        $profileError = 'Full Name, Email, and Date of Birth are required.';
    } elseif ($email !== $user->email && db_exists('users', 'email = ? AND id != ?', [$email, $uid])) {
        $profileError = 'This email address is already in use by another account.';
    } else {
        $user_type = !empty($faculty) ? 'student' : 'guest';

        // Perform updates across users and login tables
        db_update('users', [
            'name'          => $name,
            'email'         => $email,
            'faculty'       => $faculty !== '' ? $faculty : null,
            'date_of_birth' => $date_of_birth
        ], 'id = ?', [$uid]);

        if ($user->login_id) {
            db_update('login', [
                'user_type' => $user_type
            ], 'id = ?', [$user->login_id]);
        }

        $_SESSION['user_name'] = $name;
        $user->name = $name;
        $user->email = $email;
        $user->faculty = $faculty;
        $user->date_of_birth = $date_of_birth;
        $profileSuccess = 'Profile updated successfully.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $password_hash = db_fetch_value('
        SELECT l.password_hash 
        FROM users u 
        JOIN login l ON l.id = u.login_id 
        WHERE u.id = ?
    ', [$uid]);

    if (!$password_hash || !password_verify($current, $password_hash)) {
        $passwordError = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $passwordError = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $passwordError = 'New passwords do not match.';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        
        db_update('login', [
            'password_hash' => $newHash
        ], 'id = ?', [$user->login_id]);

        $passwordSuccess = 'Password changed successfully.';
    }
}

$pageTitle = 'My Account';
require 'partials/header.php';
?>
<div class="page-header">
<h1>My Account</h1>
<p>Manage your profile and password.</p>
</div>

<div class="form-card" style="margin-bottom:24px;">
<h2>Profile</h2>
<?php if ($profileError): ?><p class="alert alert-error"><?= htmlspecialchars($profileError) ?></p><?php endif; ?>
<?php if ($profileSuccess): ?><p class="alert alert-success"><?= htmlspecialchars($profileSuccess) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="form" value="profile">

<label>Username / Campus ID
<input type="text" value="<?= htmlspecialchars($user->username ?? $user->id_number ?? '') ?>" disabled readonly>
</label>

<label>Full Name <span class="required-mark">*</span> 
<input type="text" name="name" value="<?= htmlspecialchars($user->name) ?>" required>
</label>

<label>Email <span class="required-mark">*</span> 
<input type="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>
</label>

<label>Faculty / Centre
<select name="faculty">
<option value="">-- Select Faculty / Centre --</option>
<?php foreach (tarumt_faculties() as $f): ?>
<option value="<?= htmlspecialchars($f) ?>" <?= ($user->faculty ?? '') === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
<?php endforeach; ?>
</select>
</label>

<label>Date of Birth <span class="required-mark">*</span> 
<input type="date" name="date_of_birth" value="<?= htmlspecialchars($user->date_of_birth ?? '') ?>" required>
</label>

<button type="submit">Save Changes</button>
</form>
</div>

<div class="form-card">
<h2>Change Password</h2>
<?php if ($passwordError): ?><p class="alert alert-error"><?= htmlspecialchars($passwordError) ?></p><?php endif; ?>
<?php if ($passwordSuccess): ?><p class="alert alert-success"><?= htmlspecialchars($passwordSuccess) ?></p><?php endif; ?>
<form method="post">
<input type="hidden" name="form" value="password">
<label>Current Password <span class="required-mark">*</span>
<div class="password-field">
<input type="password" name="current_password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>
<label>New Password <span class="required-mark">*</span>
<div class="password-field">
<input type="password" name="new_password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>
<label>Confirm New Password <span class="required-mark">*</span>
<div class="password-field">
<input type="password" name="confirm_password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>
<button type="submit">Change Password</button>
</form>
</div>
<?php require 'partials/footer.php'; ?>