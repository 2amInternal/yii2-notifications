<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\invokeable;

use dvamigos\Yii2\Notifications\NotificationComponent;
use Yii;

abstract class EventNotification extends \yii\base\BaseObject
{
    /** @var string|callable */
    public $type = '';

    /** @var array|callable */
    public $data = [];

    /** @var string */
    public $notificationManager = 'notification';

    public function __invoke(\yii\base\Event $event)
    {
        $type = is_callable($this->type) ? call_user_func($this->type, $this) : $this->type;
        $data = is_callable($this->data) ? call_user_func($this->data, $this) : $this->data;

        $this->resolve($event, $type, $data);
    }

    /**
     * @return NotificationComponent
     * @throws \yii\base\InvalidConfigException
     */
    public function getManager()
    {
        /** @var NotificationComponent $manager */
        $manager = Yii::$app->get($this->notificationManager);
        return $manager;
    }

    public abstract function resolve(\yii\base\Event $event, $type, $data);
}