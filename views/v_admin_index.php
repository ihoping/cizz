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
    <link rel="stylesheet" href="<?= ASSETS ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS ?>/libs/WdatePicker/skin/default/datepicker.css">
    <link rel="stylesheet" href="<?= ASSETS ?>/css/styles.css?>">
</head>
<body>
<div class="wrapper-container wrap">
    <div class="wrap">
        <div class="wrap-title">全部站点</div>
        <div class="search-box justify-search">
            <button class="btn col-bgblue add-site">新增站点</button>
        </div>
        <div class="system-main">
            <table class="system-table">
                <thead>
                <tr class="col-grey">
                    <th>#</th>
                    <th>名称</th>
                    <th>链接</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($sites as $site): ?>
                    <tr>
                        <td><?= $site['id']?></td>
                        <td><?= $site['name']?></td>
                        <td><a href="<?= $site['url']?>" target="_blank"><?= $site['url']?></a></td>
                        <td><?= $site['status']?></td>
                        <td>
                            <button class="btn col-bgblue" onclick="viewData(<?= $site['id']?>)">数据</button>
                            <button class="btn col-cadetblue edit-site-layer">编辑</button>
<!--                            <button class="btn col-bgblue" onclick="delSite(--><?//= $site['id']?><!--//)">删除</button>-->
                            <button class="btn col-cadetblue" onclick="viewCode(<?= $site['id']?>)">统计代码</button>
                        </td>
                    </tr>
                <?php endforeach;?>
                </tbody>
        </div>
    </div>
    <div class="wrapper-layer J_addSite">
        <div class="layer-bg"></div>
        <div class="wrap-layer layer-box">
            <div class="layer-title">
                添加站点
                <img class="wrap-close-btn" src="<?= ASSETS?>/images/wrap-close.png">
            </div>
            <div class="layer-content">
                <div class="ly-form-group">
                    <label class="ly-form-lb">站点名称：</label>
                    <input type="text" name="" class="ipt name-input" value="" id="site_name" placeholder=""/>
                </div>
                <div class="ly-form-group">
                    <label class="ly-form-lb">站点url：</label>
                    <input type="text" name="" class="ipt name-input" value="" id="site_url" placeholder=""/>
                </div>
                <div class="confirm-btn btn col-bgblue" onclick="addSite()">添加</div>
            </div>
            <div class="layer-footer"></div>
        </div>
    </div>

    <div class="wrapper-layer J_viewCode">
        <div class="layer-bg"></div>
        <div class="wrap-layer layer-box">
            <div class="layer-title">
                复制下面的JavaScript代码到你的站点
                <img class="wrap-close-btn" src="<?= ASSETS?>/images/wrap-close.png">
            </div>
            <div class="layer-content">
                <div class="ly-form-group">
                    <textarea id="site_javascript_code" rows="4" style="width:500px"></textarea>
                </div>
            </div>
            <div class="layer-footer"></div>
        </div>
    </div>

    <div class="wrapper-layer J_editSite">
        <div class="layer-bg"></div>
        <div class="wrap-layer layer-box">
            <div class="layer-title">
                编辑站点
                <img class="wrap-close-btn" src="<?= ASSETS?>/images/wrap-close.png">
            </div>
            <div class="layer-content">
                <div class="ly-form-group">
                    <label class="ly-form-lb">站点名称：</label>
                    <span style="display: none" id="edit_site_id">0</span>
                    <input type="text" name="" class="ipt name-input" value="" id="edit_site_name" placeholder=""/>
                </div>
                <div class="ly-form-group">
                    <label class="ly-form-lb">站点url：</label>
                    <input type="text" name="" class="ipt name-input" value="" id="edit_site_url" placeholder=""/>
                </div>
                <div class="ly-form-group">
                    <label class="ly-form-lb">是否启用：</label>
                    <select class="leave-select type-select" id="edit_status">
                        <option value="1">是</option><!--1-->
                        <option value="0">否</option><!--3-->
                        <option value="3">移出列表</option>
                    </select>
                </div>
                <div class="confirm-btn btn col-bgblue" onclick="editSite()">确认更改</div>
            </div>
            <div class="layer-footer"></div>
        </div>
    </div>
</div>
</body>
<script>
    $('body').on('click', '.edit-site-layer', function (e) {
        var site_id = $(this).parent().parent().children().eq(0).text();
        $('#edit_site_id').text(site_id);
        $('#edit_site_name').val($(this).parent().parent().children().eq(1).text());
        $('#edit_site_url').val($(this).parent().parent().children().eq(2).text());
        $('#edit_status').val($(this).parent().parent().children().eq(3).text());
        $('.J_editSite').fadeIn('500');
    });

    function viewData(site_id) {
        location.href = "<?=HOSTNAME?>/admin.php?action=normal&site_id=" + site_id;
    }

    function editSite() {
        var site_id = $('#edit_site_id').text();
        var site_name = $("#edit_site_name").val();
        var site_url = $("#edit_site_url").val();
        var status = $("#edit_status").val();
        $.post('<?=HOSTNAME?>/admin.php?action=edit',
            {
                'site_id': site_id,
                'site_name': site_name,
                'site_url': site_url,
                'site_status': status
            }, function (data) {
                console.log(data);
                if (data == 'success') {
                    alert('编辑成功！')
                } else {
                    alert('编辑失败！')
                }
                location.reload();
            }
        )
    }

    function delSite(site_id) {
        if (confirm('是否删除该站点？(该操作无法恢复，如果只是停用站点请使用编辑功能更改站点状态)')) {
            $.post('<?=HOSTNAME?>/admin.php?action=del',
                {
                    'site_id': site_id,
                }, function (data) {
                    console.log(data);
                    if (data == 'success') {
                        alert('删除成功！')
                    } else {
                        alert('删除失败！')
                    }
                    location.reload();
                }
            )
        }
    }

    function viewCode(site_id) {
        var code = '<script' + ' src=\"<?= HOSTNAME?>/stat.php?site_id=' + site_id + '\">' + '<' + '/script>';
        $('#site_javascript_code').val(code);
        $('.J_viewCode').show();
    }
    function addSite() {
        var site_name = $('#site_name').val();
        var site_url = $('#site_url').val();
        if (site_name == '' || site_url == '') {
            alert('站点名称和url都不能为空');
            return;
        }

        $.post('<?=HOSTNAME?>/admin.php?action=add',
            {
                'name': site_name,
                'url': site_url
            }, function (data) {
                console.log(data);
                if (data == 'success') {
                    alert('添加成功！')
                } else {
                    alert('添加失败！')
                }
                location.reload();
            }
        )
    }
    //关闭弹窗 跟上述搭配使用
    $('.layer-bg,.wrap-close-btn').click(function () {
        $('.wrapper-layer').fadeOut('fast');
    });

    $('.add-site').click(function () {
        $('.J_addSite').show();
    })
</script>
</html>
