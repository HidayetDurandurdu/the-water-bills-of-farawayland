<?php

require_once __DIR__ . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = load_data();
    $userIndex = find_user_index_by_id($data, $_SESSION['user_id']);
    $billId = (int)($_POST['billId'] ?? 0);

    foreach ($data['users'][$userIndex]['bills'] as $index => $bill) {
        if ((int)$bill['id'] === $billId) {
            $data['users'][$userIndex]['bills'][$index]['paid'] = true;
        }
    }

    save_data($data);
}

redirect_to('/dashboard.php');

