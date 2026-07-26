<?php
require 'config.php';
require 'auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['login']);
    $password = $_POST['password'];

    // $stmt = $conn->prepare('SELECT id, name, password_hash, is_admin FROM users WHERE email = ?');
    // $stmt->bind_param('s', $email);
    // $stmt->execute();
    // $user = $stmt->get_result()->fetch_assoc();
    // $stmt->close();
    $user = sqlQuery('SELECT id, username, password_hash FROM login WHERE username = ? AND password_hash = ?',PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        header('Location: ' . ($user['is_admin'] ? 'admin/events.php' : 'index.php'));
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

$pageTitle = 'Login';
require 'partials/header.php';
?>
<div class="auth-card">
<h1>Login</h1>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<label>Username/Email <span class="required-mark">*</span> <input type="text" name="login" required></label>
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
