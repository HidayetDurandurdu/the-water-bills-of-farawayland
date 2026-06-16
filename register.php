<?php

require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = load_data();
    $errors = validate_user_fields($_POST, $data, 0);

    if (!$errors) {
        $user = [
            'id' => next_id($data, 'nextUserId'),
            'username' => trim($_POST['username']),
            'password' => password_hash((string)$_POST['password'], PASSWORD_DEFAULT),
            'waterMeterId' => strtoupper(trim($_POST['waterMeterId'])),
            'street' => trim($_POST['street']),
            'houseNumber' => (int)$_POST['houseNumber'],
            'floor' => trim($_POST['floor'] ?? ''),
            'door' => trim($_POST['door'] ?? ''),
            'readings' => [],
            'bills' => []
        ];

        $data['users'][] = $user;
        save_data($data);
        $_SESSION['user_id'] = $user['id'];
        redirect_to('/dashboard.php');
    }
}

page_header('Register');
?>
<section class="panel">
    <h1>Register</h1>

    <?php if ($errors): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <div><?= h($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= h($_POST['username'] ?? '') ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password">

        <label for="waterMeterId">Water meter ID</label>
        <input id="waterMeterId" name="waterMeterId" value="<?= h($_POST['waterMeterId'] ?? '') ?>" placeholder="QW123456">

        <label for="street">Street</label>
        <input id="street" name="street" value="<?= h($_POST['street'] ?? '') ?>">

        <label for="houseNumber">House number</label>
        <input id="houseNumber" name="houseNumber" value="<?= h($_POST['houseNumber'] ?? '') ?>">

        <div class="grid">
            <div>
                <label for="floor">Floor</label>
                <input id="floor" name="floor" value="<?= h($_POST['floor'] ?? '') ?>">
            </div>
            <div>
                <label for="door">Door</label>
                <input id="door" name="door" value="<?= h($_POST['door'] ?? '') ?>">
            </div>
        </div>

        <button type="submit">Create account</button>
    </form>
</section>
<?php page_footer(); ?>

