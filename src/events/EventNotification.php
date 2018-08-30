<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\events;

use dvamigos\Yii2\Notifications\NotificationManager;
use Yii;
use yii\base\Event;

abstract class EventNotification extends \yii\base\BaseObject
{
    /**
     * Type of the notification
     *
     * If string is passed then that notification type will be used.
     *
     * If callable function then that function will be called to retrieve the value which must be string or false.
     *
     * Callable function should be in format:
     * function($eventNotification) {
     *     return 'type';
     * }
     *
     * If value passed is false then this notification will not be resolved (i.e. resolve() will not be called).
     *
     * @var string|callable|bool
     */
    public $type = '';

    /**
     * Data for notification type which will be saved.
     *
     * If array, then that associative array will be used.
     * If callable then that function will be called to get the array value.
     *
     * Callable function should be in format:
     * function($eventNotification) {
     *     return ['mydata' => true];
     * }
     *
     * @var array|callable
     */
    public $data = [];


    /**
     * Target for notification.
     *
     * If value is null, current set target is used.
     * If value is callable, then that function is called which must return string or an array.
     * function($eventNotification) {
     *     return 'mytarget';
     * }
     *
     * If value is string then string target is used.
     * If value is an array then those targets are used.
     *
     * @see NotificationManager::$activeTarget
     * @var string|array|callable|null
     */
    public $target = null;

    /**
     * Notification manager component which will be used in getManager()
     *
     * If string then this will be resolved to manager component using Yii::$app->get()
     * If NotificationComponent then that component will be used directly.
     *
     *
     *
     * @var string|NotificationManager
     */
    public $notificationManager = 'notification';

    /**
     * @var NotificationManager
     */
    protected $manager = null;

    /**
     * Executes event notification as an event.
     *
     * @param Event $event
     * @throws \yii\base\InvalidConfigException
     * @throws \dvamigos\Yii2\Notifications\exceptions\TargetStackEmptyException
     */
    public function __invoke(\yii\base\Event $event)
    {
        $type = is_callable($this->type) ? call_user_func($this->type, $this) : $this->type;
        $data = is_callable($this->data) ? call_user_func($this->data, $this) : $this->data;
        $target = is_callable($this->target) ? call_user_func($this->target, $this) : $this->target;

        if (!is_string($type)) {
            return;
        }

        if ($target !== null) {
            $this->getManager()->pushTarget($target);
            $this->resolve($event, $type, $data);
            $this->getManager()->popTarget();
        } else {
            $this->resolve($event, $type, $data);
        }
    }

    /**
     * Returns current notification manager assigned to this event notification.
     *
     * @return NotificationManager
     * @throws \yii\base\InvalidConfigException
     */
    public function getManager()
    {
        if ($this->manager === null) {
            $this->manager = is_string($this->notificationManager) ?
                Yii::$app->get($this->notificationManager) : $this->notificationManager;
        }

        return $this->manager;
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