<?php

require_once __DIR__ . '/storage.php';

const ADMIN_USERNAME = 'admin';

$sessionDir = __DIR__ . '/../data/sessions';

if (!is_dir($sessionDir)) {
    mkdir($sessionDir);
}

session_save_path($sessionDir);
session_start();

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect_to($path)
{
    header('Location: ' . $path);
    exit;
}

function current_user()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $data = load_data();

    foreach ($data['users'] as $user) {
        if ((int)$user['id'] === (int)$_SESSION['user_id']) {
            return $user;
        }
    }

    return null;
}

function require_login()
{
    if (current_user() === null) {
        redirect_to('/login.php');
    }
}

function is_admin_user($user)
{
    return $user !== null && $user['username'] === ADMIN_USERNAME;
}

function require_admin()
{
    $user = current_user();

    if (!is_admin_user($user)) {
        redirect_to('/dashboard.php');
    }
}

function page_header($title)
{
    $user = current_user();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h($title) ?></title>
        <link rel="stylesheet" href="/assets/style.css">
    </head>
    <body>
    <header class="topbar">
        <a class="brand" href="/dashboard.php">Farawayland Water</a>
        <nav>
            <?php if ($user !== null): ?>
                <a href="/dashboard.php">Home</a>
                <?php if (is_admin_user($user)): ?>
                    <a href="/admin/index.php">Admin</a>
                <?php endif; ?>
                <a href="/logout.php">Logout</a>
            <?php else: ?>
                <a href="/login.php">Login</a>
                <a href="/register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="page">
    <?php
}

function page_footer()
{
    ?>
    </main>
    </body>
    </html>
    <?php
}

function full_address($user)
{
    $parts = [$user['street'] . ' ' . $user['houseNumber']];

    if ($user['floor'] !== '') {
        $parts[] = 'floor ' . $user['floor'];
    }

    if ($user['door'] !== '') {
        $parts[] = 'door ' . $user['door'];
    }

    return implode(', ', $parts);
}

function validate_user_fields($input, $data, $ignoreUserId)
{
    $errors = [];
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');
    $waterMeterId = strtoupper(trim($input['waterMeterId'] ?? ''));
    $street = trim($input['street'] ?? '');
    $houseNumber = trim($input['houseNumber'] ?? '');

    if ($username === '') {
        $errors[] = 'Username is required.';
    }

    foreach ($data['users'] as $user) {
        if ($user['username'] === $username && (int)$user['id'] !== (int)$ignoreUserId) {
            $errors[] = 'This username is already used.';
        }

        if ($user['waterMeterId'] === $waterMeterId && (int)$user['id'] !== (int)$ignoreUserId) {
            $errors[] = 'This water meter ID is already used.';
        }
    }

    if ($ignoreUserId === 0 && strlen($password) < 4) {
        $errors[] = 'Password must be at least 4 characters.';
    }

    if (!preg_match('/^[A-Z]{2}[0-9]{6}$/', $waterMeterId)) {
        $errors[] = 'Water meter ID must look like QW123456.';
    }

    if ($street === '') {
        $errors[] = 'Street is required.';
    }

    if (!ctype_digit($houseNumber) || (int)$houseNumber < 1) {
        $errors[] = 'House number must be a positive integer.';
    }

    return $errors;
}
