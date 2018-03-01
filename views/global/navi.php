<div class="global-tab">
    <ul class="global-tab-list">
        <li class="global-tab-item J_tab-list <?= $action == 'normal' ? 'selected' : '' ?>">
            <a href="<?= HOSTNAME . '/admin.php?site_id=' . $site_id . '&action=normal&day=' . $day?>">常规</a>
        </li>
        <li class="global-tab-item J_tab-list <?= $action == 'de' ? 'selected' : '' ?>">
            <a href="<?= HOSTNAME . '/admin.php?site_id=' . $site_id . '&action=de&day=' . $day?>">设备</a>
        </li>
        <li class="global-tab-item J_tab-list <?= $action == 'addr' ? 'selected' : '' ?>">
            <a href="<?= HOSTNAME . '/admin.php?site_id=' . $site_id . '&action=addr&day=' . $day?>">地址</a>
        </li>
    </ul>
</div>
