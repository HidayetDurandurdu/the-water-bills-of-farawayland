<?php

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_admin();

$data = load_data();
$userId = (int)($_GET['id'] ?? 0);
$userIndex = find_user_index_by_id($data, $userId);

if ($userIndex < 0) {
    redirect_to('/admin/index.php');
}

$user = $data['users'][$userIndex];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'saveUser') {
    $fakeInput = $_POST;
    $fakeInput['username'] = $user['username'];
    $fakeInput['password'] = 'kept';
    $errors = validate_user_fields($fakeInput, $data, $user['id']);

    if (!$errors) {
        $data['users'][$userIndex]['waterMeterId'] = strtoupper(trim($_POST['waterMeterId']));
        $data['users'][$userIndex]['street'] = trim($_POST['street']);
        $data['users'][$userIndex]['houseNumber'] = (int)$_POST['houseNumber'];
        $data['users'][$userIndex]['floor'] = trim($_POST['floor'] ?? '');
        $data['users'][$userIndex]['door'] = trim($_POST['door'] ?? '');
        save_data($data);
        $user = $data['users'][$userIndex];
        $success = 'User saved.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deleteReading') {
    $readingId = (int)($_POST['readingId'] ?? 0);
    $kept = [];

    foreach ($user['readings'] as $reading) {
        if ((int)$reading['id'] !== $readingId) {
            $kept[] = $reading;
        }
    }

    $data['users'][$userIndex]['readings'] = $kept;
    save_data($data);
    redirect_to('/admin/user.php?id=' . $userId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deleteBill') {
    $billId = (int)($_POST['billId'] ?? 0);
    $kept = [];

    foreach ($user['bills'] as $bill) {
        if ((int)$bill['id'] !== $billId) {
            $kept[] = $bill;
        }
    }

    $data['users'][$userIndex]['bills'] = $kept;
    save_data($data);
    redirect_to('/admin/user.php?id=' . $userId);
}

page_header('User details');
?>
<section class="panel">
    <h1><?= h($user['username']) ?></h1>
    <p class="muted">Username and password are not edited here.</p>

    <?php if ($errors): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <div><?= h($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="saveUser">

        <label for="waterMeterId">Water meter ID</label>
        <input id="waterMeterId" name="waterMeterId" value="<?= h($user['waterMeterId']) ?>">

        <label for="street">Street</label>
        <input id="street" name="street" value="<?= h($user['street']) ?>">

        <label for="houseNumber">House number</label>
        <input id="houseNumber" name="houseNumber" value="<?= h($user['houseNumber']) ?>">

        <div class="grid">
            <div>
                <label for="floor">Floor</label>
                <input id="floor" name="floor" value="<?= h($user['floor']) ?>">
            </div>
            <div>
                <label for="door">Door</label>
                <input id="door" name="door" value="<?= h($user['door']) ?>">
            </div>
        </div>

        <button type="submit">Save user</button>
        <a class="button secondary" href="/admin/index.php">Back</a>
    </form>
</section>

<section class="panel">
    <h2>Readings</h2>
    <?php if (!$user['readings']): ?>
        <p class="muted">No readings.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr><th>Date</th><th>Value</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($user['readings'] as $reading): ?>
                <tr>
                    <td><?= h($reading['datetime']) ?></td>
                    <td><?= h($reading['value']) ?> m³</td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action" value="deleteReading">
                            <input type="hidden" name="readingId" value="<?= h($reading['id']) ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Bills</h2>
    <?php if (!$user['bills']): ?>
        <p class="muted">No bills.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr><th>Period</th><th>Fee</th><th>Deadline</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($user['bills'] as $bill): ?>
                <tr>
                    <td><?= h($bill['periodStart'] ?? '-') ?> to <?= h($bill['periodEnd'] ?? '-') ?></td>
                    <td><?= h($bill['fee']) ?></td>
                    <td><?= h($bill['deadline']) ?></td>
                    <td><?= $bill['paid'] ? 'Paid' : 'Unpaid' ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="action" value="deleteBill">
                            <input type="hidden" name="billId" value="<?= h($bill['id']) ?>">
                            <button class="danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php page_footer(); ?>

