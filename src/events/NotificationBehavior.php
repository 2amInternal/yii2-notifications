<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\events;

use Yii;
use yii\base\Behavior;
use yii\base\Event;

class NotificationBehavior extends Behavior
{
    /**
     * Event list which specifies on which events these notifications will be run.
     *
     * It should be in format:
     *
     * [
     *    'eventName' => [
     *       [
     *         'class' => PushNotification::class,
     *         'type' => 'my_type'
     *       ],
     *       [
     *         'class' => PushNotification::class,
     *         'key' => 'my_type2'
     *       ],
     *    ]
     * ]
     *
     * @var array
     */
    public $events;

    /**
     * Contains cached notification instances.
     *
     * @var array
     */
    protected $cachedNotifications = [];

    public function events()
    {
        return array_fill_keys(array_keys($this->events), 'handleNotificationEvent');
    }

    public function handleNotificationEvent(Event $event)
    {
        if (empty($this->events[$event->name])) {
            return;
        }

        if (empty($this->cachedNotifications[$event->name])) {
            $notifications = $this->events[$event->name];
            $this->cachedNotifications[$event->name] = [];

            foreach ($notifications as $notificationConfig) {
                $this->cachedNotifications[$event->name][] = Yii::createObject($notificationConfig);
            }
        }

        foreach ($this->cachedNotifications[$event->name] as $notification) {
            $notification($event);
        }
    }
}