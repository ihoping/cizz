<?php

/*
 * 公共类，连接mysql和redis
 * @author:lts
 * @date:2017-10-20
 */
class Base
{
    public $redis;//redis操作类
    //public $mysql;//mysql操作类

    /*
     * $date 需为年月日时分秒类型，没有小时，$this->hour会一直为0
     */
    function __construct()
    {
        $this->redis = new Redis;
        try {
            $this->redis->connect(REDIS_HOST, REDIS_PORT);
        } catch (Exception $e) {
            die('redis连接错误master');
        }
        //$this->mysql = Factory::create('Mysqls');
    }

}
