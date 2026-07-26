<?php
require 'config.php';
require 'auth.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    $login_query =  "
                        SELECT 
                            u.id,
                            u.name,
                            l.password_hash,
                            l.user_type
                        FROM 
                            login l
                        LEFT JOIN 
                            users u
                        ON 
                            l.id = u.login_id
                        WHERE 
                            (l.username = '$login' OR u.email = '$login');
                    ";

    $user = sqlQuery($login_query,[],null,true);

    //var_dump($user);

    if ($user && password_verify($password, $user->password_hash)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['is_admin'] = !(bool)strcmp($user->user_type,"ADMIN");
        header('Location: ' . $_SESSION['is_admin'] ? 'admin/events.php' : 'index.php');
        exit;
    } else if (!$user){
        $error = 'Invalid Credential.';
    } else if ($user && !password_verify($password, $user->password_hash)){
        $error = 'Invalid Password.';
    } else {
        $error = 'Login Error.';
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
