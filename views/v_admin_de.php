<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title ?></title>
    <!-- 引入 echarts.js -->
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="keyword" content="">
    <meta name="description" content="">
    <script src="<?= ASSETS?>/js/jquery.min.js"></script>
    <script src="<?= ASSETS?>/js/bootstrap.min.js"></script>
    <script src="<?= ASSETS?>/libs/WdatePicker/WdatePicker.js"></script>
    <script src="<?= ASSETS ?>/libs/echarts/echarts.min.js"></script>
    <script src="<?= ASSETS ?>/libs/echarts/china.js"></script>
    <script src="<?= ASSETS?>/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet" href="<?= ASSETS ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS ?>/libs/WdatePicker/skin/default/datepicker.css">
    <link rel="stylesheet" href="<?= ASSETS ?>/css/styles.css?>">

</head>
<body>
<div class="wrapper-container wrap">
    <?php include_once 'global/navi.php' ?>
    <div class="wrap">
        <div class="wrap-title"><?= $subtitle ?></div>
        <div class="search-box justify-search">
            <input class="J_wdate search-select" value="<?= $day ?>" onclick="WdatePicker()">
            <select title="" class="search-select hour-select">
                <option value="99">--选择小时--</option>
                <?php for ($i = 0; $i <= 23; $i++): ?>
                    <option value="<?= $i ?>" <?php if ($hour == $i) echo 'selected' ?>><?= $i ?>点</option>
                <?php endfor; ?>
            </select>
            <select title="" class="search-select interval-select">
                <option value="99">--选择时段--</option>
                <?php for ($i = 0; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?php if ($interval == $i) echo 'selected' ?>>[<?= 10 * $i ?>分
                        - <?= 10 * $i + 10 ?>分)
                    </option>
                <?php endfor; ?>
            </select>
            <button type="button" class="btn search-btn" onclick="search()">搜 索</button>
            <a class="btn col-cadetblue" href="<?= HOSTNAME . '/admin.php' ?>">回到站点列表</a>
        </div>
        <div class="system-main" style="margin-top:50px">
            <div id="device_ip" style="width: 400px;height: 400px; display:inline-block"></div>
            <div id="device_pv" style="width: 400px;height: 400px; display:inline-block"></div>
            <div id="device_uv" style="width: 400px;height: 400px; display:inline-block"></div>
        </div>
    </div>

    <div class="wrap">
        <div class="wrap-title"><?= $subtitle?></div>
        <div class="system-main">
            <table class="system-table tablesorter">
                <thead>
                <tr class="col-grey">
                    <th  class="J_sortable">#<span class="sort-wrapper">
                            <i class="sortable up"></i>
                            <i class="sortable down"></i>
                            </span></th>
                    <?php foreach ($dis as $di): ?>
                        <th class="J_sortable"><?= $di?><span class="sort-wrapper">
                            <i class="sortable up"></i>
                            <i class="sortable down"></i>
                            </span></th>
                    <?php endforeach;?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($devices as $key => $value): ?>
                    <tr>
                        <th><?= $key?></th>
                        <?php foreach ($dis as $di): ?>
                            <td><?= $data[$value-1][$di]?></td>
                        <?php endforeach;?>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script type="text/javascript">

    $(document).ready(function () {
         $('.tablesorter').tablesorter();
    });
    var now_date = new Date();
    var init_date = new Date($('.J_wdate').val());
    if (now_date.getYear() == init_date.getYear() && now_date.getMonth() == init_date.getMonth() && now_date.getDate() == init_date.getDate()) {
        $('.interval-select').show();
    } else {
        $('.hour-select').val(99);
        $('.interval-select').val(99);
        $('.interval-select').hide();
    }

    $('body').on('mouseup', '.J_wdate', function () {
        WdatePicker({
            onpicked: function (dp) {
                var input_date = new Date(dp.cal.getNewDateStr());
                var now_date = new Date();
                if (now_date.getYear() == input_date.getYear() && now_date.getMonth() == input_date.getMonth() && now_date.getDate() == input_date.getDate()) {
                    $('.interval-select').show();
                } else {
        $('.hour-select').val(99);
        $('.interval-select').val(99);
                    $('.interval-select').hide();
                }
            }
        })
    });

    <?php foreach (array('ip', 'pv', 'uv') as $di): ?>
    var device<?= $di?>Chart = echarts.init(document.getElementById('device_<?= $di?>'));
    device_<?= $di?>_option = {
        title: {
            text: '<?= $di?>',
            left:'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: "{a} <br/>{b}: {c} ({d}%)"
        },
        legend: {
            orient: 'vertical',
            x: 'left',
            data: ['Android', 'IOS', 'PC']
        },
        series: [
            {
                name: '访问来源',
                type: 'pie',
                selectedMode: 'single',
                radius: [0, '30%'],

                label: {
                    normal: {
                        position: 'inner'
                    }
                },
                labelLine: {
                    normal: {
                        show: false
                    }
                },
                data: [
                    {value: <?php echo intval($data[1][$di]) + intval($data[2][$di])?>, name: '移动端', selected: true},
                    {value: <?php echo intval($data[0][$di])?>, name: 'PC 端'},
                ]
            },
            {
                name: '访问来源',
                type: 'pie',
                radius: ['40%', '55%'],

                data: [
                    {value: <?php echo intval($data[1][$di])?>, name: 'Android'},
                    {value: <?php echo intval($data[2][$di])?>, name: 'IOS'},
                    {value: <?php echo intval($data[0][$di])?>, name: 'PC'},
                ]
            }
        ]
    };
    device<?= $di?>Chart.setOption(device_<?= $di?>_option);
    <?php endforeach;?>

    function search() {
        var c = [];
        c.push('action=de');
        c.push('site_id=<?=$site_id?>');
        c.push('day=' + $('.J_wdate').val());
        c.push('interval=' + $('.interval-select').val());
        c.push('hour=' + $('.hour-select').val());
        location.href = '<?= HOSTNAME?>/admin.php/?' + c.join('&');
    }
    
</script>
</body>
</html>
