<?php
/**
 * Logout Handler - Dashboard Admin
 */
session_start();
$_SESSION = [];
session_destroy();
header("Location: login.php");
exit;
