<?php
require 'config.php';
require 'auth.php';
require 'helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $username_id   = trim($_POST['username_id'] ?? '');
    $faculty       = trim($_POST['faculty'] ?? '');
    $date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $username_id === '' || $password === '' || $date_of_birth === '') {
        $error = 'All required fields must be completed.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (db_exists('users', 'email = ?', [$email])) {
        $error = 'An account with this email address already exists.';
    } elseif (db_exists('login', 'username = ?', [$username_id])) {
        $error = 'This Username / Campus ID is already registered.';
    } else {
        $user_type = !empty($faculty) ? 'student' : 'guest';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $user_id = db_transaction(function() use ($username_id, $password_hash, $user_type, $name, $email, $faculty, $date_of_birth) {
                $login_id = db_insert('login', [
                    'username'      => $username_id,
                    'password_hash' => $password_hash,
                    'user_type'     => $user_type
                ]);

                return db_insert('users', [
                    'name'          => $name,
                    'email'         => $email,
                    'id_number'     => $username_id,
                    'faculty'       => $faculty !== '' ? $faculty : null,
                    'date_of_birth' => $date_of_birth,
                    'login_id'      => $login_id
                ]);
            });

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $name;
            $_SESSION['is_admin'] = false;
            header('Location: index.php');
            exit;
        } catch (Throwable $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register';
require 'partials/header.php';
?>

<style>
.tooltip-wrapper {
    position: relative;
    display: inline-block;
    width: 100%;
}
.tooltip-box {
    visibility: hidden;
    width: 280px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 4px;
    padding: 6px 10px;
    position: absolute;
    z-index: 10;
    bottom: 105%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    font-size: 0.82rem;
    line-height: 1.3;
    pointer-events: none;
}
.tooltip-wrapper:hover .tooltip-box {
    visibility: visible;
    opacity: 1;
}
.field-disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<div class="auth-card">
<h1>Create an Account</h1>
<?php if ($error): ?><p class="alert alert-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" id="register-form">

<label>Username / Campus ID (Student or Staff ID) <span class="required-mark">*</span>
<div class="tooltip-wrapper">
    <input type="text" id="username_id" name="username_id" value="<?= htmlspecialchars($_POST['username_id'] ?? '') ?>" autocomplete="off" required>
    <div class="tooltip-box">
        Notice: Use your campus-issued ID to gain access to campus exclusives and unlock campus-related fields.
    </div>
</div>
</label>
<label>Email <span class="required-mark">*</span> 
<input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
</label>

<label>Full Name <span class="required-mark">*</span> 
<input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
</label>

<label>Faculty / Centre (Campus Exclusives)
<select name="faculty" id="faculty" class="field-disabled" disabled>
<option value="">-- Select Faculty / Centre --</option>
<?php foreach (tarumt_faculties() as $f): ?>
<option value="<?= htmlspecialchars($f) ?>" <?= ($_POST['faculty'] ?? '') === $f ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
<?php endforeach; ?>
</select>
</label>

<label>Date of Birth <span class="required-mark">*</span> 
<input type="date" name="date_of_birth" value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>" required>
</label>

<label>Password <span class="required-mark">*</span>
<div class="password-field">
<input type="password" name="password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>

<label>Confirm Password <span class="required-mark">*</span>
<div class="password-field">
<input type="password" name="confirm_password" required>
<button type="button" class="password-toggle" tabindex="-1" aria-label="Show password"></button>
</div>
</label>

<button type="submit">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login here</a></p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var idInput = document.getElementById('username_id');
    var facultySelect = document.getElementById('faculty');

    function toggleCampusFields() {
        if (idInput.value.trim() !== '') {
            facultySelect.disabled = false;
            facultySelect.classList.remove('field-disabled');
        } else {
            facultySelect.disabled = true;
            facultySelect.classList.add('field-disabled');
            facultySelect.value = '';
        }
    }

    idInput.addEventListener('input', toggleCampusFields);
    toggleCampusFields();
});
</script>

<?php require 'partials/footer.php'; ?>