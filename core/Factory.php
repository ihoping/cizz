<?php

/**
 * 工厂类: 掌控全部类的初始化
 * @author lts
 */
class Factory
{
    /**
     * @param string $class_name
     * @return bool
     */
    public static function create($class_name)
    {
        if (class_exists($class_name))
            return new $class_name;
        else {
            return false;
        }
    }
}