<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\widgets\dropdown;

use dvamigos\Yii2\Notifications\widgets\NotificationWidget;
use yii\bootstrap\BootstrapAsset;

class NotificationDropdown extends NotificationWidget
{
    /**
     * Render notification dropdown.
     *
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        BootstrapAsset::register($this->getView());

        return $this->render('index', [
            'items' => $this->manager->getNotifications($this->userId)
        ]);
    }
}