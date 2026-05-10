<?php
// SUPER SIMPLE NAVBAR - Just for testing navigation
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
}
if (strpos($_SERVER['PHP_SELF'], '/locations/') !== false) {
    $base_path = '../';
}
if (strpos($_SERVER['PHP_SELF'], '/staff/') !== false) {
    $base_path = '../';
}
?>