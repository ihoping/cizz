<?php

/*
 * 用来获取一些常用信息
 * @author:lts
 * @date:2017-10-23
 */

class Info extends Base
{

    public $date;
    public $hour;
    public $interval;
    public $dis = array('ip', 'pv', 'uv');
    public $devices = array(
        'pc' => 1,
        'android' => 2,
        'ios' => 3,
    );
    public $hours = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 99);
    public $intervals = array(0, 1, 2, 3, 4, 5, 99);

    public function __construct()
    {
        parent::__construct();
        $this->date = date('ynj');
        $this->hour = date('G');
        $this->interval = $this->getInterval();
    }

    /**
     * 获取cookie的名字
     * @param string $flag
     * @return bool|string
     */
    public function getCookieName($flag = 'uni')
    {
        if ($flag == 'uni') {
            return REDIS_PREFIX . 'uni';
        } else if ($flag == 'data') {
            return REDIS_PREFIX . 'data';
        }
        return false;
    }


    /**
     * 拼接redis键名
     * @param string $flag
     * @param int $site_id
     * @param string $date
     * @return mixed|string
     */
    public function getKeyName($flag = '', $site_id = 0, $date = '')
    {
        //都是不带前导零的数字
        if (!$date) $date = $this->date;
        $common = $site_id . '_' . $date;
        $key_names = array(
            'sites' => REDIS_PREFIX . 'sites',
            'sites_count' => REDIS_PREFIX . 'sites_count',
            'normal' => REDIS_PREFIX . 'nd' . $common,//常用维度，有序集合，包含pv,ip,uv
            'device' => REDIS_PREFIX . 'de' . $common,//hash，设备
            'address' => REDIS_PREFIX . 'addr' . $common,//hash,地址信息
            'ip' => REDIS_PREFIX . 'ip' . $common,//当日ip
            'cookie' => REDIS_PREFIX . 'ck' . $common,//当日cookie，用来判断uv
        );
        return $key_names[$flag] ? $key_names[$flag] : '';
    }

    /**
     * 拼接常规维度有序集合中的成员名字(pv、uv、ip)
     * @param string $flag
     * @param bool $hour
     * @param bool $interval
     * @return bool|string
     */
    public function getNorMember($flag = '', $hour = false, $interval = false)
    {
        //都是不带前导零的数字
        if ($hour === false) $hour = $this->hour;
        if ($interval === false) $interval = $this->interval;

        if ($flag == 'uv') {
            $prefix = 'uv_';
        } else if ($flag == 'ip') {
            $prefix = 'ip_';
        } else if ($flag == 'pv') {
            $prefix = 'pv_';
        } else {
            return false;
        }
        return $prefix . $hour . '_' . $interval;
    }

    /**
     * 拼接存放设备信息的字段名称
     * @param string $flag
     * @param string $dimension
     * @param bool $hour
     * @param bool $interval
     * @return bool|string
     */
    public function getDeField($flag = '', $dimension = 'ip', $hour = false, $interval = false)
    {
        //都是不带前导零的数字
        if ($hour === false) $hour = $this->hour;
        if ($interval === false) $interval = $this->interval;

        if ($flag == 'android') {
            $prefix = 'android_';
        } else if ($flag == 'ios') {
            $prefix = 'ios_';
        } else if ($flag == 'pc') {
            $prefix = 'pc_';
        } else {
            return false;
        }

        return $prefix . $dimension . '_' . $hour . '_' . $interval;

    }

    /**
     * 拼接存放地址信息的字段名称
     * @param int $province
     * @param int $city
     * @param string $dimension
     * @param bool $hour
     * @param bool $interval
     * @return bool|string
     */
    public function getAddrField($province = 0, $city = 0, $dimension = 'ip', $hour = false, $interval = false)
    {
        if (!$province) return false;
        if ($hour === false) $hour = $this->hour;
        if ($interval === false) $interval = $this->interval;

        return $province . '_' . $city . '_' . $dimension . '_' . $hour . '_' . $interval;
    }

    /**
     * 根据ip获取地址
     * @param int $ip
     * @return array
     */
    public function getAddr($ip = 0)
    {
        $ip = intval(sprintf('%u', ip2long($ip)));
        if (!$ip) return array(0, 0);
        $n = 450000;
        $data = $this->redis->zRangeByScore('cizz_ip2addr', $ip, $ip + $n, array('limit' => [0, 1]));
        if (isset($data[0])) {
            $addr = json_decode($data[0]);
            return array($addr[2], $addr[3]);
        }
        return array(0, 0);
    }

    /**
     * 生成用户唯一标识
     * @return string
     */
    public function getUID()
    {
        $ip = getIp();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $rnd = mt_rand();
        $str = $ip . $user_agent . $rnd . time();
        return md5($str);
    }


    /**
     * 获取时间区间，60分钟分为六个区间，0-10为0,10-20为1等
     * @param bool $minute
     * @return int
     */
    public function getInterval($minute = false)
    {
        if ($minute >= 60) return 0;
        if ($minute === false) $minute = date('i');
        return intval(($minute) / 10);
    }

}
