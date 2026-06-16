<?php

require_once __DIR__ . '/includes/auth.php';

if (current_user() === null) {
    redirect_to('/login.php');
}

redirect_to('/dashboard.php');

