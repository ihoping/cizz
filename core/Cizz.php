<?php

/**
 * Class Cizz 统一入口文件
 */
class Cizz
{
    public static function run($tag = '')
    {
        session_start();
        if ($tag == 'core') {
            $core = Factory::create('Core');
            $core->log();
        } else if ($tag == 'stat') {
            $stat = Factory::create('Stat');
            $stat->response();
        } else if ($tag == 'admin') {
            $admin = Factory::create('Admin');
            $admin->route();
        }
    }
}
