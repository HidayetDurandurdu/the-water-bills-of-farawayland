<?php

require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = load_data();
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $user = find_user_by_username($data, $username);

    if ($user !== null && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        redirect_to('/dashboard.php');
    }

    $errors[] = 'Invalid username or password.';
}

page_header('Login');
?>
<section class="panel">
    <h1>Login</h1>
    <p class="muted">Seed accounts: admin / admin123, user / user123</p>

    <?php if ($errors): ?>
        <div class="errors"><?= h($errors[0]) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= h($_POST['username'] ?? '') ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password">

        <button type="submit">Login</button>
        <a class="button secondary" href="/register.php">Register</a>
    </form>
</section>
<?php page_footer(); ?>

