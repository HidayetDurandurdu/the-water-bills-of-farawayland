<?php

require_once __DIR__ . '/../includes/auth.php';

require_login();
require_admin();

$data = load_data();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $periodStart = trim($_POST['periodStart'] ?? '');
    $periodEnd = trim($_POST['periodEnd'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $baseFee = trim($_POST['baseFee'] ?? '');
    $unitFee = trim($_POST['unitFee'] ?? '');

    if ($periodStart === '' || $periodEnd === '' || $deadline === '') {
        $errors[] = 'Dates are required.';
    } elseif ($periodStart >= $periodEnd) {
        $errors[] = 'The billing period end must be after the start date.';
    } elseif ($deadline <= $periodEnd) {
        $errors[] = 'The payment deadline must be after the billing period end.';
    }

    if (!ctype_digit($baseFee) || !ctype_digit($unitFee)) {
        $errors[] = 'Fees must be integers.';
    }

    if (!$errors) {
        foreach ($data['users'] as $userIndex => $user) {
            $readings = $user['readings'];
            usort($readings, function ($a, $b) {
                return strcmp($a['datetime'], $b['datetime']);
            });

            $previous = 0;
            $consumption = 0;

            foreach ($readings as $reading) {
                $day = substr($reading['datetime'], 0, 10);

                if ($day <= $periodStart) {
                    $previous = (int)$reading['value'];
                } elseif ($day <= $periodEnd) {
                    $consumption += (int)$reading['value'] - $previous;
                    $previous = (int)$reading['value'];
                }
            }

            $fee = (int)$baseFee + $consumption * (int)$unitFee;
            $data['users'][$userIndex]['bills'][] = [
                'id' => next_id($data, 'nextBillId'),
                'fee' => $fee,
                'deadline' => $deadline,
                'paid' => false,
                'periodStart' => $periodStart,
                'periodEnd' => $periodEnd,
                'baseFee' => (int)$baseFee,
                'unitFee' => (int)$unitFee,
                'consumption' => $consumption
            ];
        }

        save_data($data);
        $success = 'Bills issued for all users.';
    }
}

$query = strtolower(trim($_GET['q'] ?? ''));
$users = [];

foreach ($data['users'] as $user) {
    $haystack = strtolower($user['waterMeterId'] . ' ' . full_address($user));

    if ($query === '' || strpos($haystack, $query) !== false) {
        $users[] = $user;
    }
}

page_header('Admin');
?>
<section class="panel">
    <h1>Admin panel</h1>
    <p class="muted">Issue bills, search users, and open user details.</p>
</section>

<section class="panel">
    <h2>Issue bills</h2>

    <?php if ($errors): ?>
        <div class="errors"><?= h($errors[0]) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="success"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="grid">
            <div>
                <label for="periodStart">Billing period start</label>
                <input id="periodStart" name="periodStart" type="date" value="<?= h($_POST['periodStart'] ?? '') ?>">
            </div>
            <div>
                <label for="periodEnd">Billing period end</label>
                <input id="periodEnd" name="periodEnd" type="date" value="<?= h($_POST['periodEnd'] ?? '') ?>">
            </div>
            <div>
                <label for="deadline">Payment deadline</label>
                <input id="deadline" name="deadline" type="date" value="<?= h($_POST['deadline'] ?? '') ?>">
            </div>
            <div>
                <label for="baseFee">Base fee</label>
                <input id="baseFee" name="baseFee" value="<?= h($_POST['baseFee'] ?? '') ?>">
            </div>
            <div>
                <label for="unitFee">Water fee per m³</label>
                <input id="unitFee" name="unitFee" value="<?= h($_POST['unitFee'] ?? '') ?>">
            </div>
        </div>
        <button type="submit">Issue bills</button>
    </form>
</section>

<section class="panel">
    <h2>Users</h2>
    <form method="get">
        <label for="q">Search by meter ID or address</label>
        <input id="q" name="q" value="<?= h($_GET['q'] ?? '') ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
        <tr><th>Username</th><th>Meter</th><th>Address</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= h($user['username']) ?></td>
                <td><?= h($user['waterMeterId']) ?></td>
                <td><?= h(full_address($user)) ?></td>
                <td><a href="/admin/user.php?id=<?= h($user['id']) ?>">Details</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php page_footer(); ?>
