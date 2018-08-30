<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 * @var $this \yii\web\View
 * @var $items dvamigos\Yii2\Notifications\NotificationInterface[]
 **/

/** @var \dvamigos\Yii2\Notifications\widgets\dropdown\NotificationDropdown $context */
$context = $this->context;
?>

<div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="true">
        <span class="glyphicon glyphicon-bell"></span>
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <?php foreach ($items as $item): ?>
            <li class="<?= $item->isRead() ? "read" : ""; ?>">
                <?= $context->renderNotificationText($item); ?>
                <span class="timestamp"><?= $context->renderNotificationTimestamp($item); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>