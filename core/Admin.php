<?php

/**
 * Class Admin
 * @author lts
 */
class Admin extends Info
{
    private $ms;//数据库对象

    public function __construct()
    {
        parent::__construct();
        $this->ms = Factory::create('MySqls');
    }

    /**
     * 统一action入口路由
     */
    public function route()
    {
        $title = 'cizz管理后台';
        $action = $_GET['action'] ? $_GET['action'] : 'index';
        if ($action == 'add') {//添加站点
            $this->addSite();
            return;
        } else if ($action == 'del') {//删除站点
            $this->delSite();
            return;
        } else if ($action == 'edit') {//编辑站点
            $this->editSite();
            return;
        } else if ($action == 'index') {//全部站点列表
            $sites = $this->index();
            include_once VIEWS . '/v_admin_index.php';
            return;
        }

        $day = $_GET['day'] ? $_GET['day'] : date('Y-m-d');
        $hour = isset($_GET['hour']) ? $_GET['hour'] : 99;//小时，0-23
        $interval = isset($_GET['interval']) ? $_GET['interval'] : 99;//时段，将一个小时分为6个时段，0-5
        $dis = $this->dis;
        $devices = $this->devices;
        $site_id = $_GET['site_id'] ? $_GET['site_id'] : false;//站点id

        if (!in_array($hour, $this->hours) || !in_array($interval, $this->intervals)) {
            echo '时间出错';
            return;
        }
        if (!$site_id) {
            echo '无站点id';
            return;
        } else {
            $site_info = $this->ms->getRow("select * from sites where id = {$site_id} limit 1");
            if (empty($site_info)) {
                echo '站点id出错';
                return;
            } else {
                $site_name = $site_info['name'];
                $site_url = $site_info['url'];
                $subtitle = $site_name . '-' . $site_url;
                if ($hour != 99) {
                    $subtitle .= '(' . $hour . '点';
                    if ($interval != 99) {
                        $subtitle .= '第' . $interval . '时段';
                    }
                    $subtitle .= '数据)';
                } else {
                    $subtitle .= '(全天数据)';
                }
            }
        }

        if ($action == 'normal') {//常规维度(ip、pv、uv)
            $data = $this->normal($day, $site_id, $hour);
        } else if ($action == 'de') {//设备
            $data = $this->de($day, $this->dis, $site_id, $hour, $interval);
        } else if ($action == 'addr') {//地址
            $data = $this->addr($day, $this->dis, $site_id, $hour, $interval);
            foreach ($data as $key => $value) {
                $max['ip'] = ($value['ip'] > $max['ip']) ? $value['ip'] : $max['ip'];
                $max['pv'] = ($value['pv'] > $max['pv']) ? $value['pv'] : $max['pv'];
                $max['uv'] = ($value['uv'] > $max['uv']) ? $value['uv'] : $max['uv'];
            }
        } else if ($action == 'normal_c') {
            $data = $this->normal_c($site_id);
        } else if ($action == 'backup') {
            $day = date('ynj', strtotime($day));
            $backup_file = VIEWS . '/normal_c/' . $site_id . '/' . $day . '.html';
            if (file_exists($backup_file)) {
                include_once VIEWS . '/normal_c/' . $site_id . '/' . $day . '.html';
            } else {
                echo $subtitle . '/在' . $day . '没有记录';
            }
            return;
        } else {
            echo 'action不存在';
            return;
        }
        include_once VIEWS . '/v_admin_' . $action . '.php';

    }

    /**
     * 全部站点列表
     * @return mixed
     *
     */
    public function index()
    {
        $sites = $this->ms->getRows("select * from sites where status != 3");
        return $sites;
    }

    /**
     * 获取ip、pv、uv常规维度数据，可选日期、时间、时段，如果日期选择的是当天，则从redis里取实时数据，否则从mysql里取已经统计好的数据
     * @param $day
     * @param $site_id
     * @param $hour
     * @return array
     */
    public function normal($day, $site_id, $hour)
    {
        if ($day == date('Y-m-d')) {//如果是当天
            $day = date('ynj', strtotime($day));
            if ($hour != 99) {//选择了小时(显示当前小时6个时段的数据)
                for ($i = 0; $i <= 5; $i++) {
                    $indexs[] = $i;
                    $ips[$i] = $this->redis->zScore($this->getKeyName('normal', $site_id, $day), $this->getNorMember('ip', $hour, $i));
                    $pvs[$i] = $this->redis->zScore($this->getKeyName('normal', $site_id, $day), $this->getNorMember('pv', $hour, $i));
                    $uvs[$i] = $this->redis->zScore($this->getKeyName('normal', $site_id, $day), $this->getNorMember('uv', $hour, $i));
                }
            } else {//没选择小时(显示当天24个小时的数据)
                for ($i = 0; $i <= 23; $i++) {
                    $ips[$i] = 0;
                    $pvs[$i] = 0;
                    $uvs[$i] = 0;
                    $indexs[] = $i;
                    for ($j = 0; $j <= 5; $j++) {
                        $ips[$i] += $this->redis->zScore($this->getKeyName('normal', $site_id, $day), $this->getNorMember('ip', $i, $j));
                        $pvs[$i] += $this->redis->zScore($this->getKeyName('normal', $site_id, $day), $this->getNorMember('pv', $i, $j));
                        $uvs[$i] += $this->redis->zScore($this->getKeyName('normal', $site_id, $day), $this->getNorMember('uv', $i, $j));
                    }
                }
            }
        } else {//如果选择的日期不是当天(显示24个小时的数据)
            $result = $this->ms->getRows("select * from stat_normal where `day` = '{$day}' and site_id = {$site_id}");
            foreach ($result as $row) {
                $indexs[] = $row['hour'];
                $ips[$row['hour']] = $row['ip'];
                $pvs[$row['hour']] = $row['pv'];
                $uvs[$row['hour']] = $row['uv'];
            }
        }
        if ($indexs) {//查到数据才执行ksort(ksort是保证小时与这个小时的数据对应)
            ksort($indexs);
            ksort($ips);
            ksort($pvs);
            ksort($uvs);
        } else {
            $indexs = array(0);
            $ips = array(0);
            $pvs = array(0);
            $uvs = array(0);
        }

        return array($indexs, $ips, $pvs, $uvs);
    }

    /**
     * 获取设备的ip、pv、uv数据，可选日期、时间、时段，如果日期选择的是当天，则从redis里取实时数据，否则从mysql里取已经统计好的数据
     * @param $day
     * @param $dis
     * @param $site_id
     * @param $hour
     * @param $interval
     * @return array
     */
    public function de($day, $dis, $site_id, $hour, $interval)
    {
        foreach ($dis as $di) {//初始化
            $androids[$di] = 0;
            $ioss[$di] = 0;
            $pcs[$di] = 0;
        }

        if ($day == date('Y-m-d')) {//选择的日期是当天
            $day = date('ynj', strtotime($day));
            if ($hour != 99) {//选择了小时
                if ($interval != 99) {//选择了小时选择了时段
                    foreach ($dis as $di) {
                        $androids[$di] = intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('android', $di, $hour, $interval)));
                        $ioss[$di] = intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('ios', $di, $hour, $interval)));
                        $pcs[$di] = intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('pc', $di, $hour, $interval)));
                    }

                } else {//选择了小时没选择时段
                    for ($i = 0; $i <= 5; $i++) {
                        foreach ($dis as $di) {
                            $androids[$di] += intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('android', $di, $hour, $i)));
                            $ioss[$di] += intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('ios', $di, $hour, $i)));
                            $pcs[$di] += intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('pc', $di, $hour, $i)));
                        }
                    }
                }
            } else {//没有选择小时(全天)
                for ($i = 0; $i <= 23; $i++) {
                    for ($j = 0; $j <= 5; $j++) {
                        foreach ($dis as $di) {
                            $androids[$di] += intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('android', $di, $i, $j)));
                            $ioss[$di] += intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('ios', $di, $i, $j)));
                            $pcs[$di] += intval($this->redis->hGet($this->getKeyName('device', $site_id, $day), $this->getDeField('pc', $di, $i, $j)));
                        }
                    }
                }
            }
        } else {//如果选择的日期不是当天
            $and = '';
            if ($hour != 99) $and .= 'and `hour`=' . $hour;//选择了小时
            $result = $this->ms->getRows("select * from stat_device where site_id = $site_id and `day` = '{$day}' {$and}");
            foreach ($result as $row) {
                if ($row['type'] == 1) {
                    foreach ($dis as $di) {
                        $pcs[$di] += $row[$di];
                    }
                }
                if ($row['type'] == 2) {
                    foreach ($dis as $di) {
                        $androids[$di] += $row[$di];
                    }
                }
                if ($row['type'] == 3) {
                    foreach ($dis as $di) {
                        $ioss[$di] += $row[$di];
                    }
                }
            }
        }
        return array($pcs, $androids, $ioss);
    }

    /**
     * 获取地址的ip、pv、uv数据，可选日期、时间、时段，如果日期选择的是当天，则从redis里取实时数据，否则从mysql里取已经统计好的数据
     * @param $day
     * @param $di
     * @param $site_id
     * @param $hour
     * @param $interval
     * @return array
     */
    public function addr($day, $dis, $site_id, $hour, $interval)
    {
        $data = array(
            '北京' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '天津' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '上海' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '重庆' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '河北' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '河南' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '云南' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '辽宁' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '黑龙江' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '湖南' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '安徽' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '山东' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '新疆' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '江苏' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '浙江' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '江西' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '湖北' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '广西' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '甘肃' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '山西' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '内蒙古' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '陕西' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '吉林' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '福建' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '贵州' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '广东' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '青海' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '西藏' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '四川' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '宁夏' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '海南' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '台湾' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '香港' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
            '澳门' => array('ip' => 0, 'pv' => 0, 'uv' => 0),
        );

        if ($day == date('Y-m-d')) {//选择的日期是当天
            $day = date('ynj', strtotime($day));//因为redis里键的日期是ynj格式
            $addr_data = $this->redis->hGetAll($this->getKeyName('address', $site_id, $day));
            if ($hour != 99) {//选择了小时
                if ($interval != 99) {//选择了小时且选择了时段
                    foreach ($addr_data as $key => $value) {
                        $info = explode('_', $key);//将key(field)分割
                        //判断时段与di是否符合
                        if ($info[3] == $hour && $info[4] == $interval) {
                            $data[$this->getShortName($info[0])][$info[2]] += $value;
                        }
                    }
                } else {//选择了小时没选择时段
                    foreach ($addr_data as $key => $value) {
                        $info = explode('_', $key);
                        //判断时段是否符合
                        if ($info[3] == $hour) {
                            $data[$this->getShortName($info[0])][$info[2]] += $value;
                        }
                    }
                }
            } else {//没有选择小时
                foreach ($addr_data as $key => $value) {
                    $info = explode('_', $key);
                    $data[$this->getShortName($info[0])][$info[2]] += $value;

                }
            }
        } else {//如果选择的日期不是当天
            $addr_data = $this->ms->getRows("select province, ip, pv, uv from stat_addr where site_id = $site_id and `day` = '{$day}'");
            foreach ($addr_data as $row) {
                foreach ($dis as $di) {
                    $data[$this->getShortName($row['province'])][$di] += $row[$di];
                }
            }
        }
        unset($data['no_p']);//删除没有省份的数据(可能是初始化时填入的)
        return $data;
    }

    /**
     * 通过地区id获取地区的short name
     * @param $p_id
     * @return string
     */
    public function getShortName($p_id)
    {
        $row = $this->ms->getRow("select short_name from district where id = {$p_id} limit 1");
        if ($row) {
            return $row['short_name'];
        } else {
            return 'no_p';
        }
    }

    /**
     * 添加站点
     */
    public function addSite()
    {
        $response = 'error';
        $name = $_POST['name'];
        $url = $_POST['url'];
        if ($name == '' || $url == '') {
            echo $response;
            exit();
        }
        $data = array(
            'name' => $name,
            'url' => $url,
            'status' => 1,
            'created' => date('Y-m-d H:i:s')
        );
        $row = $this->ms->getRow("select id from sites where name = '{$data['name']}' and url = '{$data['url']}' limit 1");
        if (!$row) {
            $insert_id = $this->ms->insert('sites', $data, true);
            if ($insert_id) {
                //将insert_id存入cizz_sites集合中
                $this->redis->sAdd($this->getKeyName('sites'), $insert_id);
                $response = 'success';
            }
        }
        echo $response;
    }

    public function delSite()
    {
        $site_id = $_POST['site_id'];
        if (!$site_id) {
            echo 'error';
            return;
        }

        $affect_num = $this->ms->query("delete from sites where id = {$site_id} limit 1", true);
        if ($affect_num) {
            //将id从cizz_sites中移出
            $this->redis->sRem($this->getKeyName('sites'), $site_id);
            echo 'success';
        } else {
            echo 'error';
        }
    }

    public function editSite()
    {
        $site_id = $_POST['site_id'];
        $site_name = $_POST['site_name'];
        $site_url = $_POST['site_url'];
        $site_status = $_POST['site_status'];

        if (!$site_id || !$site_name || !$site_url) return;

        $data = array(
            'name' => $site_name,
            'url' => $site_url,
            'status' => $site_status
        );
        $condition = 'id = ' . $site_id;

        if ($this->ms->update('sites', $condition, $data)) {
            if ($site_status == 1) {//启用时将id加入cizz_sites
                $this->redis->sAdd($this->getKeyName('sites'), $site_id);
            } else {//其他情况移出
                $this->redis->sRem($this->getKeyName('sites'), $site_id);
            }
            echo 'success';
        } else {
            echo 'error';
        }
    }

    /**
     * 当天每个小时详细表格
     * @param $site_id
     * @return mixed
     */
    public function normal_c($site_id)
    {
        $date = date('ynj');//当天
        for ($i = 0; $i <= 23; $i++) {
            for ($j = 0; $j <= 5; $j++) {
                foreach ($this->dis as $di) {
                    $num = $this->redis->zScore($this->getKeyName('normal', $site_id, $date), $this->getNorMember($di, $i, $j));
                    $num = $num ? $num : 0;
                    $data[$i][$j][$di] = $num;
                }
            }
        }
        return $data;
    }
}
