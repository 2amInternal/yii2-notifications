<?php

namespace dvamigos\Yii2\Notifications\widgets;

use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationManager;
use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/
abstract class NotificationWidget extends \yii\base\Widget
{
    /**
     * @var string|NotificationManager
     */
    public $manager = 'notification';

    /**
     * User ID for which notifications should be shown.
     *
     * @var null|int
     */
    public $userId = null;

    /**
     * Item template
     *
     * Allowed string:
     * {text} - only available when right side of notification type in manager is string
     * {text.key} - only available when right side of the notification type in manager is associative array.
     *              key represents the key of that array.
     *
     * @var string
     */
    public $itemTemplate = '{text}';

    /**
     * Timestamp format in Formatter format.
     *
     * @var string
     */
    public $timestampFormat = 'php:m/d/Y H:i:s';


    protected $compiledTemplate = null;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->manager = Instance::ensure($this->manager, NotificationManager::class);
        $this->compileTemplate();
    }


    public function renderNotificationText(NotificationInterface $item)
    {
        $data = ['text' => $item->getCompiledText()];

        $replacements = [];
        foreach ($this->compiledTemplate as $item) {
            $replacements["\{{$item}\}"] = ArrayHelper::getValue($data, $item);
        }

        return strtr($this->itemTemplate, $replacements);
    }

    public function renderNotificationTimestamp(NotificationInterface $notification)
    {
        return Yii::$app->getFormatter()->asDatetime($notification->getTimestamp(), $this->timestampFormat);
    }

    protected function compileTemplate()
    {
        preg_match_all("/\{([^\}]+)\}/gi", $this->itemTemplate, $matches);
        $this->compiledTemplate = $matches[1];
    }
}