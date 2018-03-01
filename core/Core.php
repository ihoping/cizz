<?php

/*
 * 接受前端的http请求并进行统计数据
 * @author:lts
 * @date:2017-10-23
 */

class Core extends Info
{
    private $ip;
    private $uid;//唯一id
    private $province;
    private $city;

    public function __construct()
    {
        parent::__construct();
        $this->ip = getIp() ? getIp() : 0;
        if ($_COOKIE[$this->getCookieName('uni')]) {
            $this->uid = $_COOKIE[$this->getCookieName('uni')];
        } else {
            $this->uid = md5(getIp() . $_SERVER['HTTP_USER_AGENT']);
        }

        //cookie里有地址等数据时就直接在cookie里取，否则去redis里取
        if ($_COOKIE[$this->getCookieName('data')]) {
            $data = json_decode($_COOKIE[$this->getCookieName('data')], true);
            $this->province = $data['province'];
            $this->city = $data['city'];
        } else {
            $data = $this->getAddr(getIp());
            $this->province = $data[0];
            $this->city = $data[1];
        }

    }

    //记录
    public function log()
    {
        $site_id = intval($_GET['site_id']);
        if (!$site_id) die('var error_code = 100001');//site id wrong

        if (!$this->redis->sIsMember($this->getKeyName('sites'), $site_id)) die('var error_code = 100002;');//site id don't exist

        //记录pv
        $this->pv($site_id);

        //记录设备信息(也包括记录设备pv、uv、ip，必须放在记录uv、ip前)
        $this->de($site_id);

        //记录地址信息(也包括pv、uv、ip，必须放在记录uv、ip前)
        $this->addr($site_id);

        //记录ip
        $this->ip($site_id);

        //记录cookie(uv)
        $this->uv($site_id);
    }

    /**
     * 记录pv
     * @param $site_id
     */
    public function pv($site_id)
    {
        $this->redis->zIncrBy($this->getKeyName('normal', $site_id), 1, $this->getNorMember('pv'));
    }

    public function uv($site_id)
    {
        $res = $this->redis->sAdd($this->getKeyName('cookie', $site_id), $this->uid);
        if ($res) $this->redis->zIncrBy($this->getKeyName('normal', $site_id), 1, $this->getNorMember('uv'));
    }

    public function ip($site_id)
    {
        if ($this->ip) {
            $res = $this->redis->sAdd($this->getKeyName('ip', $site_id), $this->ip);
            if ($res) $this->redis->zIncrBy($this->getKeyName('normal', $site_id), 1, $this->getNorMember('ip'));
        }
    }

    public function de($site_id)
    {
        $de = $_GET['de'] ? $_GET['de'] : 'other';
        //记录设备pv
        $this->redis->hIncrBy($this->getKeyName('device', $site_id), $this->getDeField($de, 'pv'), 1);
        //记录设备uv
        if (!$this->redis->sIsMember($this->getKeyName('cookie', $site_id), $this->uid)) {
            $this->redis->hIncrBy($this->getKeyName('device', $site_id), $this->getDeField($de, 'uv'), 1);
        }
        //记录设备ip
        if ($this->ip) {
            if (!$this->redis->sIsMember($this->getKeyName('ip', $site_id), $this->ip)) {
                $this->redis->hIncrBy($this->getKeyName('device', $site_id), $this->getDeField($de, 'ip'), 1);
            }
        }
    }

    /**
     * @param int $site_id
     */
    public function addr($site_id)
    {
        if ($this->province) {
            //记录地址pv
            $this->redis->hIncrBy($this->getKeyName('address', $site_id), $this->getAddrField($this->province, $this->city, 'pv'), 1);
            //记录地址uv
            if (!$this->redis->sIsMember($this->getKeyName('cookie', $site_id), $this->uid)) {
                $this->redis->hIncrBy($this->getKeyName('address', $site_id), $this->getAddrField($this->province, $this->city, 'uv'), 1);
            }
            //记录地址ip
            if (!$this->redis->sIsMember($this->getKeyName('ip', $site_id), $this->ip)) {
                $this->redis->hIncrBy($this->getKeyName('address', $site_id), $this->getAddrField($this->province, $this->city, 'ip'), 1);
            }
        }
    }
}
