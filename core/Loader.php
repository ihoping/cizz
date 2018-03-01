<?php

/**
 * 自动加载类库
 * @author lts
 */
class Loader
{
    /**
     * 自动加载
     * @static
     * @param $class_name
     * @return bool
     */
    static function load($class_name)
    {
        //自动加载类库
        $class_name = str_replace('\\', '/', $class_name);
        $file = CORE . '/' . $class_name . '.php';
        if (is_file($file)) {
            include $file;
        } else {
            return false;
        }
    }
}