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
    <?php include_once 'global/navi.php'?>
    <div class="wrap">
        <div class="wrap-title"><?= $subtitle?></div>
        <div class="search-box justify-search">
            <input class="Wdate search-select J_wdate" value="<?=$day?>" onclick="WdatePicker()">
            <select title="" class="search-select hour-select" style="display: none">
                <option value="99">--选择小时--</option>
                <?php for ($i = 0; $i <= 23; $i++): ?>
                    <option value="<?= $i?>" <?php if ($hour == $i) echo 'selected'?>><?= $i?>点</option>
                <?php endfor; ?>
            </select>
            <button type="button" class="btn search-btn" onclick="search()">搜 索</button>
            <button type="button" class="btn search-btn col-darkkhaki" onclick="historySearch()">历史记录</button>
            <a class="btn col-cadetblue" href="<?= HOSTNAME . '/admin.php'?>">回到站点列表</a>
        </div>
        <div class="system-main">
            <div id="normal" style="width: auto;height: 500px"></div>
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
                <?php $data_lists = $data;?>
                <?php while($data_lists[0]): ?>
                    <tr>
                        <td><?= array_shift($data_lists[0])?></td>
                        <td><?= array_shift($data_lists[1])?></td>
                        <td><?= array_shift($data_lists[2])?></td>
                        <td><?= array_shift($data_lists[3])?></td>
                    </tr>
                <?php endwhile;?>
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
        $('.hour-select').show();
    } else {
        $('.hour-select').val(99);
        $('.interval-select').val(99);
        $('.hour-select').hide();
    }

    $('body').on('mouseup', '.J_wdate', function () {
        WdatePicker({
            onpicked: function (dp) {
                var input_date = new Date(dp.cal.getNewDateStr());
                var now_date = new Date();
                if (now_date.getYear() == input_date.getYear() && now_date.getMonth() == input_date.getMonth() && now_date.getDate() == input_date.getDate()) {
                    $('.hour-select').show();
                } else {
                    $('.hour-select').val(99);
                    $('.interval-select').val(99);
                    $('.hour-select').hide();
                }
            }
        })
    });

    var norChart = echarts.init(document.getElementById('normal'));
    nor_option = {
        title: {
            text: ''
        },
        color: ['#3398DB', '#33dbbf', '#a7db33'],
        tooltip: {
            trigger: 'axis',
            axisPointer: {            // 坐标轴指示器，坐标轴触发有效
                type: 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        legend: {
            data: ['ip', 'pv', 'uv']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: [
            {
                type: 'category',
                data: <?php echo '[' . implode(',', $data[0]) . ']'?>,
                axisTick: {
                    alignWithLabel: true
                }
            }
        ],
        yAxis: [
            {
                type: 'value'
            }
        ],
        series: [
            {
                name: 'ip',
                type: 'bar',
                barWidth: '20%',
                data: <?php echo '[' . implode(',', $data[1]) . ']'?>,
            },
            {
                name: 'pv',
                type: 'bar',
                barWidth: '20%',
                data: <?php echo '[' . implode(',', $data[2]) . ']'?>,
            },
            {
                name: 'uv',
                type: 'bar',
                barWidth: '20%',
                data: <?php echo '[' . implode(',', $data[3]) . ']'?>,
            }
        ]
    };
    // 使用刚指定的配置项和数据显示图表。
    norChart.setOption(nor_option);

    function search() {
        var c = [];
        c.push('action=normal');
        c.push('site_id=<?=$site_id?>');
        c.push('day=' + $('.J_wdate').val());
        c.push('hour=' + $('.hour-select').val());
        location.href = '<?= HOSTNAME?>/admin.php/?' + c.join('&');
    }

    function historySearch() {
        var c = [];
        c.push('action=backup');
        c.push('site_id=<?=$site_id?>');
        c.push('day=' + $('.J_wdate').val());
        location.href = '<?= HOSTNAME?>/admin.php/?' + c.join('&');
    }
</script>
</body>
</html>
