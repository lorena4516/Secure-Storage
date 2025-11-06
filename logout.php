<?php
require_once 'app/utils/Auth.php';
Auth::logout();
header('Location: login.php');
exit;
?>