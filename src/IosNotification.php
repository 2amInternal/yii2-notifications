<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

use yii\helpers\ArrayHelper;

class IosNotification extends Notification
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
     * Alert sound
     *
     * @var string
     */
    public $sound = 'default';

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function getBody()
    {
        return [
            'alert' => $this->getIosAlertData(),
            'sound' => $this->sound
        ];
    }

    protected function getIosAlertData()
    {
        $text = $this->getIosCompiledText();

        return [
            'title' => ArrayHelper::getValue($text, $this->titleParam, '[unknown title]'),
            'body' => ArrayHelper::getValue($text, $this->bodyParam, '[unknown body]'),
        ];
    }

    protected function getIosCompiledText()
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