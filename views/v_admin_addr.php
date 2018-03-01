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
        <div class="wrap-title"><?= $subtitle?></div>
        <div class="search-box justify-search">
            <input class="J_wdate search-select" value="<?= $day ?>" onclick="WdatePicker()">
            <select title="" class="search-select hour-select">
                <option value="99">--选择小时--</option>
                <?php for ($i = 0; $i <= 23; $i++): ?>
                    <option value="<?= $i ?>" <?php if ($hour == $i) echo 'selected'?>><?= $i ?>点</option>
                <?php endfor; ?>
            </select>
            <select title="" class="search-select interval-select">
                <option value="99">--选择时段--</option>
                <?php for ($i = 0; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?php if ($interval == $i) echo 'selected'?>>[<?= 10 * $i ?>分 - <?= 10 * $i + 10 ?>分)</option>
                <?php endfor; ?>
            </select>
            <button type="button" class="btn search-btn" onclick="search()">搜 索</button>
            <a class="btn col-cadetblue" href="<?= HOSTNAME . '/admin.php' ?>">回到站点列表</a>
        </div>
        <div class="system-main">
            <div id="address_ip" style="width: auto;height: 500px"></div>
            <div id="address_pv" style="width: auto;height: 500px"></div>
            <div id="address_uv" style="width: auto;height: 500px"></div>
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
                <?php foreach ($data as $key => $value): ?>
                <tr>
                    <th><?= $key?></th>
                    <?php foreach ($dis as $di): ?>
                    <td><?= $value[$di]?></td>
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
        $('.interval-select, .hour-select').show();
    } else {
        $('.hour-select').val(99);
        $('.interval-select').val(99);
        $('.interval-select, .hour-select').hide();
    }

    $('body').on('mouseup', '.J_wdate', function () {
        WdatePicker({
            onpicked: function (dp) {
                var input_date = new Date(dp.cal.getNewDateStr());
                var now_date = new Date();
                if (now_date.getYear() == input_date.getYear() && now_date.getMonth() == input_date.getMonth() && now_date.getDate() == input_date.getDate()) {
                    $('.interval-select,.hour-select').show();
                } else {
                    $('.hour-select').val(99);
                    $('.interval-select').val(99);
                    $('.interval-select,.hour-select').hide();
                }
            }
        })
    });

    <?php foreach ($dis as $di): ?>
    var addr<?=$di?>Chart = echarts.init(document.getElementById('address_<?= $di?>'));

    addr_<?=$di?>option = {
        title: {
            text: '访客地域分布(<?=$di?>)',
            subtext: '',
            left: 'center'
        },
        tooltip: {
            trigger: 'item'
        },
        visualMap: {
            min: 0,
            max: <?= $max[$di] ? $max[$di] : 200?>,
            left: 'left',
            top: 'bottom',
            text: ['高', '低'],           // 文本，默认为数值文本
            calculable: true
        },
        toolbox: {
            show: true,
            orient: 'vertical',
            left: 'right',
            top: 'center',
            feature: {
                dataView: {readOnly: false},
                restore: {},
                saveAsImage: {}
            }
        },
        series: [
            {
                name: '总量',
                type: 'map',
                mapType: 'china',
                roam: false,
                label: {
                    normal: {
                        show: true
                    },
                    emphasis: {
                        show: true
                    }
                },
                data: [
                    <?php foreach ($data as $key => $value): ?>
                    {name: '<?= $key?>', value: <?= $value[$di]?>},
                    <?php endforeach;?>
                ]
            },

        ]
    };
    addr<?=$di?>Chart.setOption(addr_<?=$di?>option);
    <?php endforeach;?>
    function search() {
        var c = [];
        c.push('action=addr');
        c.push('site_id=<?=$site_id?>');
        c.push('day=' + $('.J_wdate').val());
        c.push('interval=' + $('.interval-select').val());
        c.push('hour=' + $('.hour-select').val());
        location.href = '<?= HOSTNAME?>/admin.php/?' + c.join('&');
    }
</script>
</body>
</html>
