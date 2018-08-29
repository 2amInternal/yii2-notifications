<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\invokeable;

use dvamigos\Yii2\Notifications\NotificationComponent;
use Yii;
use yii\di\Instance;

class ReplaceNotification extends EventNotification
{
    /** @var callable|int */
    public $replaceId;

    /**
     * Pushes notification to using notification manager.
     *
     * @param \yii\base\Event $event
     * @param $type
     * @param $data
     * @throws \dvamigos\Yii2\Notifications\exceptions\SaveFailedException
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function resolve(\yii\base\Event $event, $type, $data)
    {
        $id = is_callable($this->replaceId) ? call_user_func($this->replaceId, $this) : $this->replaceId;
        $this->getManager()->replace($id, $type, $data);
    }
}