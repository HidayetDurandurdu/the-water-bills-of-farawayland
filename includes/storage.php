<?php

function data_file_path()
{
    return __DIR__ . '/../data/data.json';
}

function default_data()
{
    return [
        'nextUserId' => 3,
        'nextReadingId' => 3,
        'nextBillId' => 3,
        'users' => [
            [
                'id' => 1,
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'waterMeterId' => 'AD123456',
                'street' => 'Council Street',
                'houseNumber' => 1,
                'floor' => '',
                'door' => '',
                'readings' => [
                    ['id' => 1, 'value' => 60, 'datetime' => '2026-03-15 10:00:00']
                ],
                'bills' => []
            ],
            [
                'id' => 2,
                'username' => 'user',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
                'waterMeterId' => 'UW123456',
                'street' => 'Main street',
                'houseNumber' => 67,
                'floor' => '2',
                'door' => '5',
                'readings' => [
                    ['id' => 2, 'value' => 120, 'datetime' => '2026-04-20 14:55:50']
                ],
                'bills' => [
                    [
                        'id' => 1,
                        'fee' => 3400,
                        'deadline' => '2026-06-07',
                        'paid' => false,
                        'periodStart' => '2026-03-01',
                        'periodEnd' => '2026-04-30',
                        'baseFee' => 400,
                        'unitFee' => 25,
                        'consumption' => 120
                    ],
                    [
                        'id' => 2,
                        'fee' => 900,
                        'deadline' => '2026-04-01',
                        'paid' => false,
                        'periodStart' => '2026-01-01',
                        'periodEnd' => '2026-02-28',
                        'baseFee' => 900,
                        'unitFee' => 20,
                        'consumption' => 0
                    ]
                ]
            ]
        ]
    ];
}

function load_data()
{
    $path = data_file_path();

    if (!file_exists($path)) {
        $data = default_data();
        save_data($data);
        return $data;
    }

    $json = file_get_contents($path);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        $data = default_data();
        save_data($data);
    }

    return $data;
}

function save_data($data)
{
    $path = data_file_path();
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir);
    }

    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
}

function find_user_index_by_id($data, $id)
{
    foreach ($data['users'] as $index => $user) {
        if ((int)$user['id'] === (int)$id) {
            return $index;
        }
    }

    return -1;
}

function find_user_by_username($data, $username)
{
    foreach ($data['users'] as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }

    return null;
}

function next_id(&$data, $name)
{
    $id = $data[$name];
    $data[$name] = $id + 1;
    return $id;
}
