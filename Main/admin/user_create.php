<?php
require '../config.php';
require '../auth.php';
require '../helpers.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Name, email and password are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (db_exists('users', 'email = ?', [$email])) {
        $error = 'An account with this email address already exists.';
    } else {
        $username = explode('@', $email)[0] . '_' . rand(100, 999);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            db_transaction(function() use ($username, $password_hash, $name, $email) {
                $login_id = db_insert('login', [
                    'username'      => $username,
                    'password_hash' => $password_hash,
                    'user_type'     => 'admin'
                ]);

                return db_insert('users', [
                    'name'     => $name,
                    'email'    => $email,
                    'login_id' => $login_id
                ]);
            });

            header('Location: users.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Failed to create admin account. Please try again.';
        }
    }
}

$pageTitle = 'Add Admin';
require 'partials/header.php';
?>
<div class="form-card">
<h1>Add Admin</h1>
<p>Creates a new account that already has admin access — no self-registration or promotion needed.</p>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<label>Full Name <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required></label>
<label>Email <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required></label>
<label>Password
<div class="password-field">
<input type="password" name="password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>
<label>Confirm Password
<div class="password-field">
<input type="password" name="confirm_password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>
<button type="submit">Create Admin Account</button>
</form>
<div class="card-actions">
<a class="btn btn-secondary btn-small" href="users.php">Back to Users</a>
</div>
</div>
<?php require 'partials/footer.php'; ?>
