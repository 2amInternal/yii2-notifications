<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\events;

class PushNotification extends EventNotification
{
    /**
     * Pushes notification to using notification manager.
     *
     * @param \yii\base\Event $event
     * @param $type
     * @param $data
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function resolve(\yii\base\Event $event, $type, $data)
    {
        $this->getManager()->push($type, $data);
    }
}