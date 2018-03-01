<?php
/**
 * 注册自动加载
 * @author:lts
 */

ini_set('display_errors', '1');
error_reporting(E_ALL^E_NOTICE);

require_once 'config.php';
require_once 'function.php';
require_once CORE . '/' . 'Loader.php';
spl_autoload_register('Loader::load');
