<?php

/**
 * 脚本
 */
class Cron extends Info
{
    private $link;

    public function __construct()
    {
        parent::__construct();
        //连接mysql
        $this->link = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die(mysqli_connect_error());
        mysqli_query($this->link, 'set names utf8');

    }

    /**
     * 统计前一天的流量数据(0:15执行)
     * @param int $yesterday
     */
    public function total($yesterday = 0)
    {
        if (!$yesterday) $yesterday = date('ynj', strtotime('-1 day'));
        //获取所有站点
        $sites_id = $this->redis->sMembers($this->getKeyName('sites'));

        if (!empty($sites_id)) {
            foreach ($sites_id as $site_id) {
                //统计常规pv、ip、uv(每个小时所记录的)
                $nor_data = $this->getNormalInfo($yesterday, $site_id);
                foreach ($nor_data as $key => $value) {
                    $ips = $value['ips'] ? $value['ips'] : 0;
                    $pvs = $value['pvs'] ? $value['pvs'] : 0;
                    $uvs = $value['uvs'] ? $value['uvs'] : 0;
                    $sql = "select id from stat_normal where `day` = '{$yesterday}' and `hour`={$key} and site_id={$site_id} and ip={$ips} and pv={$pvs} and uv={$uvs} limit 1";
                    $result = mysqli_query($this->link, $sql);//数据是否已经存在
                    if (!$result->num_rows) {//不存在
                        $sql = "select id from stat_normal where `day`='{$yesterday}' and `hour`={$key} and site_id={$site_id} limit 1";
                        $result = mysqli_query($this->link, $sql);//数据已经存在但数据不对
                        $row = mysqli_fetch_assoc($result);
                        if (isset($row['id'])) {
                            $sql = "update stat_normal set ip={$ips},pv={$pvs},uv={$uvs} where id = {$row['id']} limit 1";
                            mysqli_query($this->link, $sql);
                        } else {
                            $sql = "insert into stat_normal (`day`, `hour`, site_id, ip, pv, uv) values ('{$yesterday}', {$key}, {$site_id}, {$ips}, {$pvs}, {$uvs})";
                            mysqli_query($this->link, $sql);
                        }
                        echo $sql . "\n";
                    }
                }

                //统计设备维度
                $de_data = $this->getDeInfo($yesterday, $site_id);
                if ($de_data) {
                    foreach ($de_data as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            $ips = $value1['ip'] ? $value1['ip'] : 0;
                            $pvs = $value1['pv'] ? $value1['pv'] : 0;
                            $uvs = $value1['uv'] ? $value1['uv'] : 0;
                            $sql = "select id from stat_device where `day`='{$yesterday}' and `hour`={$key1} and site_id={$site_id} and `type`={$key} and ip={$ips} and pv={$pvs} and uv={$uvs} limit 1";
                            $result = mysqli_query($this->link, $sql);//数据是否已经存在
                            if (!$result->num_rows) {//不存在
                                $sql = "select id from stat_device where `day`='{$yesterday}' and `hour`={$key1} and site_id={$site_id} and `type`={$key} limit 1";
                                $result = mysqli_query($this->link, $sql);
                                $row = mysqli_fetch_assoc($result);
                                if (isset($row['id'])) {
                                    $sql = "update stat_device set ip={$ips},pv={$pvs},uv={$uvs} where id = {$row['id']} limit 1";
                                    mysqli_query($this->link, $sql);
                                } else {
                                    $sql = "insert into stat_device (`day`, `hour`, site_id, `type`, ip, pv, uv) values ('{$yesterday}', {$key1}, {$site_id}, {$key}, {$ips}, {$pvs}, {$uvs})";
                                    mysqli_query($this->link, $sql);
                                }
                                echo $sql . "\n";
                            }
                        }
                    }
                }
                //统计地址维度
                $addr_data = $this->getAddrInfo($yesterday, $site_id);
                if ($addr_data) {
                    foreach ($addr_data as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            $ips = $value1['ip'] ? $value1['ip'] : 0;
                            $pvs = $value1['pv'] ? $value1['pv'] : 0;
                            $uvs = $value1['uv'] ? $value1['uv'] : 0;
                            $sql = "select id from stat_addr where `day`='{$yesterday}' and site_id=$site_id and province=$key and city=$key1 and ip={$ips} and pv={$pvs} and uv={$uvs} limit 1";
                            $result = mysqli_query($this->link, $sql);
                            if (!$result->num_rows) {
                                $sql = "select id from stat_addr where `day`='{$yesterday}' and site_id=$site_id and province=$key and city=$key1 limit 1";
                                $result = mysqli_query($this->link, $sql);
                                $row = mysqli_fetch_assoc($result);
                                if (isset($row['id'])) {
                                    $sql = "update stat_addr set ip={$ips},pv={$pvs},uv={$uvs} where id = {$row['id']} limit 1";
                                    mysqli_query($this->link, $sql);
                                } else {
                                    $sql = "insert into stat_addr (`day`, site_id, province, city, ip, pv, uv) values ('{$yesterday}', $site_id, {$key}, {$key1}, {$ips}, {$pvs}, {$uvs})";
                                    mysqli_query($this->link, $sql);
                                }
                                echo $sql . "\n";
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $date
     * @param $site_id
     * @return array
     */
    private function getNormalInfo($date, $site_id)
    {
        $data = array();
        for ($i = 0; $i <= 23; $i++) {
            $p_n[$i] = 0;
            $u_n[$i] = 0;
            $i_n[$i] = 0;
            for ($j = 0; $j <= 5; $j++) {
                //$this->getKeyName('pv', $site_id, $yesterday, $hour, $j) . "\n";
                $pvs = $this->redis->zScore($this->getKeyName('normal', $site_id, $date), $this->getNorMember('pv', $i, $j));
                $pvs = $pvs ? $pvs : 0;
                $p_n[$i] += $pvs;
                $uvs = $this->redis->zScore($this->getKeyName('normal', $site_id, $date), $this->getNorMember('uv', $i, $j));
                $uvs = $uvs ? $uvs : 0;
                $u_n[$i] += $uvs;
                $ips = $this->redis->zScore($this->getKeyName('normal', $site_id, $date), $this->getNorMember('ip', $i, $j));
                $ips = $ips ? $ips : 0;
                $i_n[$i] += $ips;
            }
            $data[$i] = array(
                'pvs' => $p_n[$i],
                'ips' => $i_n[$i],
                'uvs' => $u_n[$i]
            );
        }
        return $data;
    }

    /**
     * @param $date
     * @param int $site_id
     * @return mixed
     */
    private function getDeInfo($date, $site_id)
    {
        $device_data = $this->redis->hGetAll($this->getKeyName('device', $site_id, $date));
        foreach ($device_data as $key => $value) {
            $info = explode('_', $key);
            $device = $this->devices[$info[0]] ? $this->devices[$info[0]] : 0;
            $data[$device][$info[2]][$info[1]] += $value;
        }
        return $data;
    }

    private function getAddrInfo($date, $site_id)
    {
        $addr_data = $this->redis->hGetAll($this->getKeyName('address', $site_id, $date));
        foreach ($addr_data as $key => $value) {
            $info = explode('_', $key);
            $data[$info[0]][$info[1]][$info[2]] += $value;
        }
        return $data;
    }

    /**
     * 初始化第二天的key并设置有效期（23点50执行）
     * @param int $tomorrow
     */
    public function initKey($tomorrow = 0)
    {
        if (!$tomorrow) $tomorrow = date('ynj', strtotime('+1 day'));

        $sites_id = $this->redis->sMembers($this->getKeyName('sites'));
        if (!empty($sites_id)) {
            foreach ($sites_id as $site_id) {
                //初始化normal
                $this->redis->zAdd($this->getKeyName('normal', $site_id, $tomorrow), 0, $this->getNorMember('pv', 0, 0));
                if ($this->redis->expireAt($this->getKeyName('normal', $site_id, $tomorrow), time() + 3 * 24 * 60 * 60)) {
                    echo 'zadd_' . $this->getKeyName('normal', $site_id, $tomorrow) . '_' . date('Ymd') . "\n";
                }

                //初始化存放设备ip、pv、uv数的键
                $this->redis->hSet($this->getKeyName('device', $site_id, $tomorrow), $this->getDeField('ios', 'ip', 0, 0), 0);
                if ($this->redis->expireAt($this->getKeyName('device', $site_id, $tomorrow), time() + 3 * 24 * 60 * 60)) {
                    echo 'hSet_' . $this->getKeyName('device', $site_id, $tomorrow) . '_' . date('Ymd') . "\n";
                }
                //初始化存放地址ip、pv、uv数的键
                $this->redis->hSet($this->getKeyName('address', $site_id, $tomorrow), $this->getAddrField(1000, 1000, 'ip', 0, 0), 0);
                if ($this->redis->expireAt($this->getKeyName('address', $site_id, $tomorrow), time() + 3 * 24 * 60 * 60)) {
                    echo 'hSet_' . $this->getKeyName('address', $site_id, $tomorrow) . '_' . date('Ymd') . "\n";
                }

                //初始化存放cookie的键
                $this->redis->sAdd($this->getKeyName('cookie', $site_id, $tomorrow), 0);
                if ($this->redis->expireAt($this->getKeyName('cookie', $site_id, $tomorrow), time() + 1.25 * 24 * 60 * 60)) {
                    echo 'sAdd_' . $this->getKeyName('cookie', $site_id, $tomorrow) . '_' . date('Ymd') . "\n";
                }

                //初始化存放ip的键
                $this->redis->sAdd($this->getKeyName('ip', $site_id, $tomorrow), 0);
                if ($this->redis->expireAt($this->getKeyName('ip', $site_id, $tomorrow), time() + 1.25 * 24 * 60 * 60)) {
                    echo 'sAdd_' . $this->getKeyName('ip', $site_id, $tomorrow) . '_' . date('Ymd') . "\n";
                }
            }
        }
    }

    /**
     * 将cizz数据库中district表转移到redis cizz_district中
     */
    public function addr2redis()
    {
        return;
        $sql = "select * from `district`";
        $query = mysqli_query($this->link, $sql);

        //转移数据
        while ($row = mysqli_fetch_assoc($query)) {
            $d_info = json_encode(array($row['id'], $row['name']));
            if ($this->redis->zAdd('cizz_district', $row['id'], $d_info)) {
                echo 'insert member-' . $d_info . 'score-' . $row['id'] . 'to cizz_district success' . "\n";
            } else {
                echo 'insert member-' . $d_info . 'score-' . $row['id'] . 'to cizz_district error' . "\n";
            }
        }
    }

    /**
     * 将cizz库中cizz_ip2addr逐行读出存入redis cizz_ip2addr键中
     */
    public function ip2redis()
    {
        return;
        //转移数据，每次4万条
        $max_ip = -1;
        while (true) {
            $sql = "select * from ip2addr where id > {$max_ip} limit 40000";
            $query = mysqli_query($this->link, $sql);
            if (mysqli_affected_rows($this->link) == 0)
                break;

            while ($row = mysqli_fetch_assoc($query)) {
                if (!empty($row) && $row['province'] == 0 && $row['city'] == 0) {
                    continue;
                }
                $ip_addr_info = json_encode(array($row['start'], $row['end'], $row['province'], $row['city']));
                echo $this->redis->zAdd('cizz_ip2addr', $row['end'], $ip_addr_info) . "\n";
                $max_ip = $row['id'];
            }
        }
    }

    /**
     * 将纯真ip库文本文件按行读取存入mysql中
     */
    public function ip2table()
    {
        return;
        $sql = "select * from `district`";
        $query = mysqli_query($this->link, $sql);

        while ($row = mysqli_fetch_assoc($query)) {
            if ($row['level'] == 1) {
                $provinces[$row['id']] = $row['short_name'];
            } elseif ($row['level'] == 2) {
                $citys[$row['id']] = $row['short_name'];
                $citys_p[$row['id']] = $row['parent_id'];
            }
        }

        $file = 'data.txt';
        $handle = fopen($file, "r");
        if (!$handle) {
            die("ip文件读取失败");
        }

        while (!feof($handle)) {
            $row = fgets($handle, 4096);//逐行读取
            $row = trim(preg_replace("/[\s]+/is", " ", $row));//将多个空格合并成一个空格
            // die();
            $row = explode(" ", $row);

            $start_ip = sprintf('%u', ip2long($row[0]));//无符号输出
            $end_ip = sprintf('%u', ip2long($row[1]));

            $province = 0;
            $city = 0;
            $area = $row[2];
            $detail = $row[3];
            foreach ($provinces as $pid => $name) {
                if (strpos($area, $name) !== false) {
                    $province = $pid;
                    break;
                }
            }
            foreach ($citys as $cid => $name) {
                if (strpos($area, $name) !== false) {
                    $city = $cid;
                    $province = $province ? $province : $citys_p[$cid];
                    break;
                }
            }
            $sql = "insert into ip2addr (start,end,province,city,country,local) values({$start_ip},{$end_ip},{$province},{$city},'{$area}','{$detail}')";
            echo $sql . "\n";
            $result = mysqli_query($this->link, $sql);
            echo $result;
        }
        fclose($handle);
    }

    /**
     * 生成当天每个小时常规数据的html并保存
     */
    public function normal2html()
    {
        $filename = date('ynj') . '.html';
        $c_str = '_p=c738d3f7cdee0acad1ac3ea5af6c9ce7';
        $user_agent = 'Mozilla / 5.0 (Windows NT 6.1; Win64; x64) AppleWebKit / 537.36 (KHTML, like Gecko) Chrome / 61.0.3163.91 Safari / 537.36';
        $sites_id = $this->redis->sMembers($this->getKeyName('sites'));
        foreach ($sites_id as $site_id) {
            $file_path = VIEWS . '/normal_c/' . $site_id;
            if (!is_dir($file_path)) mkdir($file_path);
            $url = "http://cizz.ciurl.cn/admin.php/?action=normal_c&site_id={$site_id}";
            $content = curl($c_str, $user_agent, $url);
            file_put_contents($file_path . '/' . $filename, $content);
        }
    }
}
