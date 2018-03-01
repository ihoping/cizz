<?php

/**
 * 生成第三方cookie并返回js
 * @author lts
 */
class Stat extends Info
{
    public function response()
    {
        $site_id = intval($_GET['site_id']);
        if (!$site_id) die('var error_code = 100001');//site id wrong

        if (!isset($_COOKIE[$this->getCookieName('data')])) {
            setcookie($this->getCookieName('data'), $this->mergeCC(), time() + 3600 * 24 * 100, '/');
        }
        if (!isset($_COOKIE[$this->getCookieName('uni')])) {
            setcookie($this->getCookieName('uni'), $this->getUID(), time() + 3600 * 24 * 100, '/');
        }
        $site_id = intval($_GET['site_id']);

        echo "(function () {
            var site_id = '$site_id';
            var de = d();
            var c = [];
            c.push('site_id=' + site_id);
            c.push('de=' + de);
            c.push('rnd=' + Math.floor(2147483648 * Math.random()));
            function d()
            {
                if (/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)) {
                    return 'ios';
                } else if (/(Android)/i.test(navigator.userAgent)) {
                    return 'android';
                } else {
                    return 'pc';
                }
            }
            (new Image).src = 'http://cizz.ciurl.cn/core.php?' + c.join('&');
    })();";
    }

    public function mergeCC()
    {
        $ip = getIp();
        $addr = $this->getAddr($ip);
        $data = array(
            'ip' => $ip,
            'province' => $addr[0],
            'city' => $addr[1]
        );
        return json_encode($data);
    }

}
