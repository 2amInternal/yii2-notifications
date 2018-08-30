<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\invokeable;

use dvamigos\Yii2\Notifications\NotificationManager;
use Yii;
use yii\base\Event;
use yii\di\Instance;

class ReplaceNotification extends EventNotification
{
    /**
     * ID of the notification which will be replaced.
     *
     * If value is numeric then that value will be used.
     *
     * If callable then that function will be called to get the ID. ID must be a numeric value.
     *
     * Callable function should be in format:
     * function($eventNotification) {
     *     return 1; // Notification ID
     * }
     *
     * @var callable|int
     */
    public $replaceId;

    /**
     * Replaces existing notification using notification manager.
     *
     * @param \yii\base\Event $event
     * @param $type
     * @param $data
     * @throws \dvamigos\Yii2\Notifications\exceptions\SaveFailedException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function resolve(Event $event, $type, $data)
    {
        $id = is_callable($this->replaceId) ? call_user_func($this->replaceId, $this) : $this->replaceId;
        $this->getManager()->replace($id, $type, $data);
    }
}