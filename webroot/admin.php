<?php
require_once '../inc/global.php';
$p = md5('cizz1949');
if ($_COOKIE['_p'] != $p) {
    header('location:' . HOSTNAME . '/login.php');
} else {
    Cizz::run('admin');
}
