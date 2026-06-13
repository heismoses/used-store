<?php
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
redirect(SITE_URL . '/auth/login.php');
