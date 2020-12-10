<?php

defined('_JEXEC') or die('Restricted access');

echo JHtml::_('bootstrap.addTab', 'yamTab', 'updater-tab', 'Обновления');

?>

<div class="row">
    <div class="span11 offset1">
        <h4><?= _JSHOP_YOO_UPDATER_TEXT_HEADER ?></h4>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <p>
            <?= _JSHOP_YOO_UPDATER_ABOUT_TEXT ?>
        </p>
        <p>
            <?= _JSHOP_YOO_UPDATER_DISABLED_TEXT ?>
            <?php if (!$zip_enabled && !$curl_enabled): ?>
                <?= _JSHOP_YOO_UPDATER_CAUSE_ZIP_CURL ?>
            <?php elseif (!$zip_enabled) : ?>
                <?= _JSHOP_YOO_UPDATER_CAUSE_ZIP ?>
            <?php elseif (!$curl_enabled) : ?>
                <?= _JSHOP_YOO_UPDATER_CAUSE_CURL ?>
            <?php endif; ?>
        </p>
    </div>
</div>
