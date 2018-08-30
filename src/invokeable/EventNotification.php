<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\invokeable;

use dvamigos\Yii2\Notifications\NotificationComponent;
use Yii;
use yii\base\Event;

abstract class EventNotification extends \yii\base\BaseObject
{
    /**
     *  Type of the notification
     *
     *  If string is passed then that notification type will be used.
     *
     *  If callable function then that function will be called to retrieve the value which must be string or false.
     *
     *  Callable function should be in format:
     *  function($eventNotification) {
     *      return 'type';
     *  }
     *
     *  If value passed is false then this notification will not be resolved (i.e. resolve() will not be called).
     *
     *  @var string|callable|bool
     */
    public $type = '';

    /**
     *  Data for notification type which will be saved.
     *
     *  If array, then that associative array will be used.
     *  If callable then that function will be called to get the array value.
     *
     *  Callable function should be in format:
     *  function($eventNotification) {
     *      return ['mydata' => true];
     *  }
     *
     *  @var array|callable
     */
    public $data = [];

    /**
     * Notification manager component which will be used in getManager()
     *
     * If string then this will be resolved to manager component using Yii::$app->get()
     * If NotificationComponent then that component will be used directly.
     *
     * @var string|NotificationComponent
     */
    public $notificationManager = 'notification';

    public function __invoke(\yii\base\Event $event)
    {
        $type = is_callable($this->type) ? call_user_func($this->type, $this) : $this->type;
        $data = is_callable($this->data) ? call_user_func($this->data, $this) : $this->data;

        if (!is_string($type)) {
            return;
        }

        $this->resolve($event, $type, $data);
    }

    /**
     * Returns current notification manager assigned to this event notification.
     *
     * @return NotificationComponent
     * @throws \yii\base\InvalidConfigException
     */
    public function getManager()
    {
        /** @var NotificationComponent $manager */
        $manager = is_string($this->notificationManager) ? Yii::$app->get($this->notificationManager) : $this->notificationManager;
        return $manager;
    }

    /**
     * Resolves the notification when this notification its called.
     *
     * @param Event $event Event object on which this notification is called
     * @param $type string Type of the notification.
     * @param $data array Data of the notification
     */
    public abstract function resolve(Event $event, $type, $data);
}