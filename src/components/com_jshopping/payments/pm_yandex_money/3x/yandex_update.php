<?php

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'updater-tab', 'Обновления');

?>

<div class="row">
    <div class="span11 offset1">
        <p>
            Здесь будут появляться новые версии модуля — с новыми возможностями или с исправленными ошибками.
            Чтобы установить новую версию модуля, нажмите кнопку «Обновить».
        </p>
        <h4>О модуле:</h4>
        <ul>
            <li>Установленная версия модуля — <?php echo _JSHOP_YM_VERSION; ?></li>
            <li>Последняя версия модуля — <?php echo $newVersion; ?></li>
            <li>
                Последняя проверка наличия новых версий — <?php echo $newVersionInfo['date'] ?>
                <?php if (time() - $newVersionInfo['time'] > 300) : ?>
                    <button type="button" class="btn btn-success btn-xs" id="force-check">Проверить наличие обновлений</button>
                <?php endif; ?>
            </li>
        </ul>

        <?php if ($new_version_available) : ?>

            <h4>История изменений:</h4>
            <p><?php echo $changelog; ?></p>

            <button type="button" id="update-module" class="btn btn-primary">Обновить</button>
        <?php else: ?>
            <p>Установлена последняя версия модуля.</p>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($backups)) : ?>
<div class="row">
    <div class="span11 offset1">
        <h4>Резервные копии</h4>
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>Версия модуля</th>
                <th>Дата создания</th>
                <th>Имя файла</th>
                <th>Размер файла</th>
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
                        <button type="button" class="btn btn-success btn-xs restore-backup" data-id="<?php echo $backup['name'] ?>" data-version="<?php echo $backup['version'] ?>" data-date="<?php echo $backup['date'] ?>">Восстановить</button>
                        <button type="button" class="btn btn-danger btn-xs remove-backup" data-id="<?php echo $backup['name'] ?>" data-version="<?php echo $backup['version'] ?>" data-date="<?php echo $backup['date'] ?>">Удалить</button>
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
            if (window.confirm('Вы действительно хотите обновить модуль до последней версии?')) {
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
            if (window.confirm('Вы действительно хотите удалить резервную копию "' + this.dataset.id + '"?')) {
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
            if (window.confirm('Вы действительно хотите восстановить резервную копию "' + this.dataset.id + '"?')) {
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
