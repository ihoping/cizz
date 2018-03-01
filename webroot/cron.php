<?php
/**
 * 运行脚本
 */

if ('cli' !== PHP_SAPI) {
    die('ERR_NEED_CLI');
}

require_once '../inc/global.php';

$f_name = $argv[1];
if (!$f_name)
    die('ERR_NO_PARAMETER');

$cron = Factory::create('Cron');
if (method_exists($cron, $f_name)) {
    $cron->$f_name($argv[2]);
} else {
    echo 'ERR_NO_Method_' . $f_name;
}
