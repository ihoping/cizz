<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title ?></title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="keyword" content="">
    <meta name="description" content="">
    <script src="<?= ASSETS ?>/js/jquery.min.js"></script>
    <script src="<?= ASSETS ?>/js/bootstrap.min.js"></script>
    <script src="<?= ASSETS ?>/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet" href="<?= ASSETS ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= ASSETS ?>/css/styles.css?>">
</head>
<body>
<div class="wrapper-container wrap">
    <div class="wrap">
        <div class="wrap-title"><?= $subtitle ?></div>
        <div class="system-main">
            <table class="system-table tablesorter">
                <thead>
                <tr class="col-grey">
                    <th>#</th>
                    <th colspan="6" class="J_sortable">ip<span class="sort-wrapper"><i class="sortable up"></i><i class="sortable down"></i></span></th>
                    <th colspan="6" class="J_sortable">pv<span class="sort-wrapper"><i class="sortable up"></i><i class="sortable down"></i></span></th>
                    <th colspan="6" class="J_sortable">uv<span class="sort-wrapper"><i class="sortable up"></i><i class="sortable down"></i></span></th>
                </tr>
                <tr class="col-grey">
                    <td>#</td>
                    <?php for ($i = 0; $i <= 2; $i++): ?>
                        <?php for ($j = 0; $j <= 5; $j++): ?>
                            <td><?= $j ?></td>
                        <?php endfor; ?>
                    <?php endfor; ?>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i <= 23; $i++): ?>
                    <tr>
                    <td><?= $i?></td>
                    <?php foreach ($dis as $di): ?>
                        <?php for ($j = 0; $j <= 5; $j++): ?>
                                <td><?= $data[$i][$j][$di]?></td>
                        <?php endfor; ?>
                    <?php endforeach;?>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
<script>
 $(document).ready(function () {
    $('.tablesorter').tablesorter();
 });
</script>
</html>
