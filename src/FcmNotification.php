<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

use yii\helpers\ArrayHelper;

class FcmNotification extends Notification
{
    /**
     * Default title if notification is string type.
     * @var string
     */
    public $defaultTitle = '';

    /**
     * Title key of the notification type
     *
     * @var string
     */
    public $titleParam = 'title';

    /**
     * Body key of the notification type
     *
     * @var string
     */
    public $bodyParam = 'message';

    /**
     * Token data param
     *
     * @var string
     */
    public $tokenDataParam = 'token';

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function getFcmRequestData()
    {
        return [
            'to' => $this->getToken(),
            'notification' => $this->getNotificationData(),
            'data' => $this->getAdditionalData()
        ];
    }

    public function getToken()
    {
        return $this->getData()[$this->tokenDataParam];
    }

    protected function getAdditionalData()
    {
        return $this->getData()['additional'] ?? [];
    }

    protected function getNotificationData()
    {
        $text = $this->getNotificationText();

        return [
            'title' => ArrayHelper::getValue($text, $this->titleParam, '[unknown title]'),
            'body' => ArrayHelper::getValue($text, $this->bodyParam, '[unknown body]'),
            'click_action' => 'OPEN_NOTIFY_ACTIVITY'
        ];
    }

    protected function getNotificationText()
    {
        $text = $this->getCompiledText();

        if (is_string($text)) {
            $text = [
                $this->bodyParam => $text,
                $this->titleParam => $this->defaultTitle
            ];
        }

        return $text;
    }
}