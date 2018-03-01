(function () {
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
    (new Image).src = 'http://cizz.xxxx.cn/core.php?' + c.join('&');
})();