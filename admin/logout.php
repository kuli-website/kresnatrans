<?php
session_start();
require_once __DIR__ . '/includes/auth_helper.php';

logoutUser();
header('Location: login.php');
exit;

