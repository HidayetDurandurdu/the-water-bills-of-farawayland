<?php

require_once __DIR__ . '/includes/auth.php';

require_login();

$data = load_data();
$userIndex = find_user_index_by_id($data, $_SESSION['user_id']);
$user = $data['users'][$userIndex];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $value = trim($_POST['reading'] ?? '');
    $lastValue = 0;
    $lastDatetime = '';

    foreach ($user['readings'] as $reading) {
        if ($reading['datetime'] > $lastDatetime) {
            $lastDatetime = $reading['datetime'];
            $lastValue = (int)$reading['value'];
        }
    }

    if (!ctype_digit($value)) {
        $errors[] = 'Reading must be an integer.';
    } elseif ((int)$value < $lastValue) {
        $errors[] = 'Reading cannot be lower than the last reported reading.';
    } else {
        $data['users'][$userIndex]['readings'][] = [
            'id' => next_id($data, 'nextReadingId'),
            'value' => (int)$value,
            'datetime' => date('Y-m-d H:i:s')
        ];
        save_data($data);
        $user = $data['users'][$userIndex];
        $success = 'Reading saved.';
    }
}

$unpaidTotal = 0;
foreach ($user['bills'] as $bill) {
    if (!$bill['paid']) {
        $unpaidTotal += (int)$bill['fee'];
    }
}

$readings = $user['readings'];
usort($readings, function ($a, $b) {
    return strcmp($b['datetime'], $a['datetime']);
});

page_header('Dashboard');
?>
<section class="panel">
    <h1>Hello, <?= h($user['username']) ?></h1>
    <p>
        Water meter: <strong><?= h($user['waterMeterId']) ?></strong><br>
        Address: <?= h(full_address($user)) ?>
    </p>
    <div class="debt">Unpaid debt: <?= h($unpaidTotal) ?></div>
</section>

<div class="grid">
    <section class="panel">
        <h2>Report meter reading</h2>

        <?php if ($errors): ?>
            <div class="errors"><?= h($errors[0]) ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="success"><?= h($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="reading">Current meter reading</label>
            <input id="reading" name="reading" inputmode="numeric">
            <button type="submit">Save reading</button>
        </form>
    </section>

    <section class="panel">
        <h2>Previous reports</h2>
        <?php if (!$readings): ?>
            <p class="muted">No readings yet.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr><th>Date</th><th>Value</th></tr>
                </thead>
                <tbody>
                <?php foreach ($readings as $reading): ?>
                    <tr>
                        <td><?= h($reading['datetime']) ?></td>
                        <td><?= h($reading['value']) ?> m³</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>

<section class="panel">
    <h2>Your bills</h2>
    <?php if (!$user['bills']): ?>
        <p class="muted">No bills issued yet.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Period</th>
                <th>Consumption</th>
                <th>Fee</th>
                <th>Deadline</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($user['bills'] as $bill): ?>
                <?php $overdue = !$bill['paid'] && $bill['deadline'] < date('Y-m-d'); ?>
                <tr class="<?= $overdue ? 'overdue' : '' ?>">
                    <td><?= h($bill['periodStart'] ?? '-') ?> to <?= h($bill['periodEnd'] ?? '-') ?></td>
                    <td><?= h($bill['consumption'] ?? 0) ?> m³</td>
                    <td><?= h($bill['fee']) ?></td>
                    <td><?= h($bill['deadline']) ?></td>
                    <td><?= $bill['paid'] ? 'Paid' : 'Unpaid' ?></td>
                    <td>
                        <?php if (!$bill['paid']): ?>
                            <form method="post" action="/pay_bill.php">
                                <input type="hidden" name="billId" value="<?= h($bill['id']) ?>">
                                <button type="submit">Pay</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php page_footer(); ?>
