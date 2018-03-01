<?php
/**
 * @author:lts
 * @version 1.0
 */

/**
 * 获取ip地址
 * @return string
 */
function getIp()
{
    $ip = '';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(', ', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($ips as $v) {
            $v = trim($v);
            if (!preg_match('/^(10|172\.16|192\.168)\./', $v)) {
                if (strtolower($v) != 'unknown') {
                    $ip = $v;
                    break;
                }
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if (!preg_match('/[\d\.]{7,15}/', $ip)) {
        $ip = '';
    }
    return $ip;
}

/**
 * 获取用户的地址
 * @param string $ip
 * @return array|bool|mixed
 */
function getAddress($ip = '')
{
    if ($ip == '') {
        $url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json";
        $ip = json_decode(file_get_contents($url), true);
        $data = $ip;
    } else {
        $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
        $ip = json_decode(file_get_contents($url));
        if ((string)$ip->code == '1') {
            return false;
        }
        $data = (array)$ip->data;
    }
    return $data;
}

/**
 * 获取参数
 * @return mixed
 */
function getParams()
{
    if (isset($_GET)) {
        foreach (array_keys($_GET) as $key) {
            $vars[$key] = $_GET[$key];
        }
    }
    if (isset($_POST)) {
        foreach (array_keys($_POST) as $key) {
            $vars[$key] = $_POST[$key];
        }
    }
    return $vars;
}

/**
 * @param $url
 * @param $params
 * @return mixed|string
 */
function urlReplace($url, $params)
{
    global $base_params;
    if (empty($params) || !is_array($params)) {
        return $url;
    }
    foreach ($params as $k => $v) {
        if (isset($base_params[$k])) {
            $url = str_replace($base_params[$k], $v, $url);
        }
    }
    if (strpos($url, 'adclick') !== false) {
        foreach ($base_params as $k => $v) {
            if (isset($params[$k]) && $params[$k] !== false) {
                $url .= "&{$k}={$params[$k]}";
            }
        }
    }
    return $url;
}

/**
 * @param $msg
 * @param string $back_url
 */
function redirect($msg, $back_url = '')
{
    if ($msg) {
        echo "<script type='text/javascript'>alert('{$msg}');</script>";
    }
    if ($back_url) {
        echo "<script type='text/javascript'>location.href='{$back_url}';</script>";
    } else {
        echo "<script type='text/javascript'>history.back();</script>";
    }
    die();
}

/**
 * @return false|int
 */
function getEndTimestamp()
{
    return strtotime(date('Y-m-d 23:59:59'));
}

/**
 * @param $cookie
 * @param $user_agent
 * @param $destURL
 * @param string $paramStr
 * @param string $flag
 * @param string $ip
 * @param string $fromurl
 * @return mixed
 */
function curl($cookie, $user_agent, $destURL, $paramStr = '', $flag = 'get', $ip = '218.94.95.62', $fromurl = 'http://cizz.ciurl.com')
{
    $curl = curl_init();
    if ($flag == 'post') {//post传递
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $paramStr);
    }
    curl_setopt($curl, CURLOPT_URL, $destURL);//地址

    curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:' . $ip, 'CLIENT-IP:' . $ip));  //构造IP

    curl_setopt($curl, CURLOPT_REFERER, $fromurl);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);#10s超时时间

    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
    //curl_setopt ($curl, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($curl, CURLOPT_COOKIE, $cookie);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $str = curl_exec($curl);
    curl_close($curl);
    return $str;
}
