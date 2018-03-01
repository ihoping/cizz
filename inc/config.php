<?php
/**
 * 配置文件
 * @author:lts
 */
define("CIZZ", realpath('../'));//项目目录
define("CORE", CIZZ . '/core');//核心类文件目录
define("HOSTNAME", 'http://cizz.xxxx.cn');
define("ASSETS", HOSTNAME . '/assets');
define("VIEWS", CIZZ . '/views');//模板目录

define("DB_HOST", '192.168.1.10:3307');//数据库配置
define("DB_USERNAME", 'xxx');
define("DB_PASSWORD", 'xxxx!@#');
define("DB_NAME", 'cizz');

define("DB_SLAVE_HOST", '192.168.1.10:3307');//SLAVE数据库配置
define("DB_SLAVE_USERNAME", 'xxxx');
define("DB_SLAVE_PASSWORD", 'xxxx!@#');
define("DB_SLAVE_NAME", 'cizz');

//Redis的配置
define("REDIS_HOST", '192.168.1.7');
define("REDIS_PORT", '6382');

define("REDIS_SLAVE_HOST", '192.168.1.7');
define("REDIS_SLAVE_PORT", '6382');

//define("REDIS_HOST", '192.168.1.251');
//define("REDIS_PORT", '6381');

define("DB_PREFIX", 'cizz_');
define("REDIS_PREFIX", 'cizz_');

//记录logs的路径
define("LOG_PATH", realpath('../logs'));
