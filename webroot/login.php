<?php
    require_once '../inc/global.php';
    $p = md5('cizz1949');
    if ($_COOKIE['_p'] == $p || ($_GET['p'] && md5($_GET['p']) == $p)) {
        setcookie('_p', $p, time()+24*7*60*60);
        header('location:' . HOSTNAME . '/admin.php');
        exit();
    }
?>
<script>
    function verify() {
        var password = prompt("请输入口令");
        location.href = '<?= HOSTNAME?>' + '/login.php?p=' + password;
    }
    verify();
</script>
