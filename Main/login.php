<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$error = '';
$identity = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = db_fetch_one('
        SELECT 
            l.id AS login_id, 
            l.username, 
            l.password_hash, 
            l.user_type, 
            u.id AS user_id, 
            u.name, 
            u.email
        FROM login l
        LEFT JOIN users u ON u.login_id = l.id
        WHERE l.username = ? OR u.email = ?
        LIMIT 1
    ', [$identity, $identity]);

    if ($user && password_verify($password, $user->password_hash)) {
        $_SESSION['user_id'] = $user->user_id ?? $user->login_id;
        $_SESSION['user_name'] = $user->name ?? $user->username;
        $_SESSION['is_admin'] = ($user->user_type === 'admin');

        header('Location: ' . ($_SESSION['is_admin'] ? 'admin/dashboard.php' : 'index.php'));
        exit;
    } else {
        $error = 'Invalid username/email or password.';
    }
}

$pageTitle = 'Login';
require 'partials/header.php';
?>
<div class="auth-card">
<h1>Login</h1>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<label>Username or Email <span class="required-mark">*</span> 
<input type="text" name="identity" value="<?= htmlspecialchars($identity) ?>" required autofocus>
</label>
<label>Password <span class="required-mark">*</span>
<div class="password-field">
<input type="password" name="password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>
<button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
<?php require 'partials/footer.php'; ?>