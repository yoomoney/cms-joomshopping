<?php

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'updater-tab', _JSHOP_YM_TAB_UPDATE);

?>

<div class="row">
    <div class="span11 offset1">
        <p>
            <?= _JSHOP_YM_UPDATER_HEADER_TEXT ?>
        </p>
        <h4><?= _JSHOP_YM_UPDATER_ABOUT ?></h4>
        <ul>
            <li><?= _JSHOP_YM_UPDATER_CURRENT_VERSION ?><?php echo _JSHOP_YM_VERSION; ?></li>
            <li><?= _JSHOP_YM_UPDATER_LAST_VERSION ?><?php echo $newVersion; ?></li>
            <li>
                <?= _JSHOP_YM_UPDATER_LAST_CHECK?> <?php echo $newVersionInfo['date'] ?>
                <?php if (time() - $newVersionInfo['time'] > 300) : ?>
                    <button type="button" class="btn btn-success btn-xs" id="force-check"><?= _JSHOP_YM_UPDATER_CHECK?>
                    </button>
                <?php endif; ?>
            </li>
        </ul>

        <?php if ($new_version_available) : ?>

            <h4><?= _JSHOP_YM_HISTORY_LABEL?></h4>
            <p><?php echo $changelog; ?></p>

            <button type="button" id="update-module" class="btn btn-primary"><?= _JSHOP_YM_UPDATE_LABEL?></button>
        <?php else: ?>
            <p><?= _JSHOP_YM_INSTALL_MESSAGE?></p>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($backups)) : ?>
    <div class="row">
        <div class="span11 offset1">
            <h4><?= _JSHOP_YM_BACKUPS_LABEL?></h4>
            <table class="table table-striped table-hover">
                <thead>
                <tr>
                    <th><?= _JSHOP_YM_MODULE_VERSION_LABEL?></th>
                    <th><?= _JSHOP_YM_BACKUP_DATE_CREATE?></th>
                    <th><?= _JSHOP_YM_BACKUP_FILE_NAME?></th>
                    <th><?= _JSHOP_YM_BACKUP_FILE_SIZE?></th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($backups as $backup) : ?>
                    <tr>
                        <td><?php echo $backup['version'] ?></td>
                        <td><?php echo $backup['date'] ?></td>
                        <td><?php echo $backup['name'] ?></td>
                        <td><?php echo $backup['size'] ?></td>
                        <td class="text-right">
                            <button type="button" class="btn btn-success btn-xs restore-backup"
                                    data-id="<?php echo $backup['name'] ?>"
                                    data-version="<?php echo $backup['version'] ?>"
                                    data-date="<?php echo $backup['date'] ?>"><?= _JSHOP_YM_UPDATER_RESTORE?>
                            </button>
                            <button type="button" class="btn btn-danger btn-xs remove-backup"
                                    data-id="<?php echo $backup['name'] ?>"
                                    data-version="<?php echo $backup['version'] ?>"
                                    data-date="<?php echo $backup['date'] ?>"><?= _JSHOP_YM_UPDATER_DELETE?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#update-module').click(function () {
            var form = document.getElementById('adminForm');
            var paymentId = form.payment_id.value;
            if (window.confirm('<?= _JSHOP_YM_UPDATER_APPROVE_ACTION_MESSAGE?>')) {
                jQuery.ajax({
                    method: 'GET',
                    url: 'index.php',
                    data: {
                        option: 'com_jshopping',
                        controller: 'payments',
                        task: 'edit',
                        payment_id: paymentId,
                        subaction: 'update'
                    },
                    dataType: 'json',
                    success: function (res) {
                        alert(res.message);
                        if (res.success) {
                            document.location = document.location;
                        }
                    }
                });
            }
        });
        jQuery('.remove-backup').click(function () {
            var form = document.getElementById('adminForm');
            var paymentId = form.payment_id.value;
            if (window.confirm('<?= _JSHOP_YM_UPDATER_APPROVE_DELETE_MESSAGE?> "' + this.dataset.id + '"?')) {
                jQuery.ajax({
                    method: 'POST',
                    url: 'index.php?option=com_jshopping&controller=payments&task=edit&subaction=remove_backup&payment_id=' + paymentId,
                    data: {
                        file_name: this.dataset.id
                    },
                    dataType: 'json',
                    success: function (res) {
                        alert(res.message);
                        if (res.success) {
                            document.location = document.location;
                        }
                    }
                });
            }
        });
        jQuery('.restore-backup').click(function () {
            var form = document.getElementById('adminForm');
            var paymentId = form.payment_id.value;
            if (window.confirm('<?= _JSHOP_YM_UPDATER_APPROVE_RESTORE_MESSAGE?> "' + this.dataset.id + '"?')) {
                jQuery.ajax({
                    method: 'POST',
                    url: 'index.php?option=com_jshopping&controller=payments&task=edit&subaction=restore_backup&payment_id=' + paymentId,
                    data: {
                        file_name: this.dataset.id
                    },
                    dataType: 'json',
                    success: function (res) {
                        alert(res.message);
                        if (res.success) {
                            document.location = document.location;
                        }
                    }
                });
            }
        });
    });
</script>
