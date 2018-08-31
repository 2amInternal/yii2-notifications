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
class NotificationList extends \yii\base\Widget
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
     * Container template for listing notifications.
     *
     * Available:
     * {notifications} - Lists all notifications in that place.
     *
     * If this is callable then this function will be called and it must return a string result.
     * This result will not be processed for template strings.
     *
     * Callback is in format:
     * function($notifications, NotificationList $widget) {
     *     return "Result.";
     * }
     *
     * @var string|callable
     */
    public $containerTemplate = "{notifications}";

    /**
     * Item template
     *
     * Allowed string:
     * {text} - only available when right side of notification type in manager is string
     * {text.key} - only available when right side of the notification type in manager is associative array.
     *              key represents the key of that array.
     * {timestamp} - formatted timestamp
     * {section.key} - renders a section from $sections list. Where key is section name.
     * {notification.key} - renders a value from notification object directly. Key represents parameter from notification object.
     *
     * @see NotificationWidget::$sections
     * @var string
     */
    public $itemTemplate = '{notification.type} at {timestamp}';

    /**
     * Rendering sections
     *
     * Should be in format:
     * [
     *     'key' => function(NotificationItem $item) {
     *          return 'result';
     *     }
     * ]
     *
     * @var array
     */
    public $sections = [];

    /**
     * Timestamp format in Formatter format.
     *
     * @var string
     */
    public $timestampFormat = 'php:m/d/Y H:i:s';


    /**
     * Glue for joining notifications when rendering
     *
     * @var string
     */
    public $listGlue = PHP_EOL;


    protected $templateReplacements = null;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->manager = Instance::ensure($this->manager, NotificationManager::class);
        $this->compileTemplateReplacements();
    }


    public function run()
    {
        $notifications = $this->manager->getNotifications($this->userId);

        if (is_callable($this->containerTemplate)) {
            return call_user_func_array($this->containerTemplate, [$notifications, $this]);
        }

        return strtr($this->containerTemplate, [
            '{notifications}' => implode($this->listGlue, array_map(function (NotificationInterface $n) {
                return $this->renderNotificationText($n);
            }, $notifications))
        ]);
    }

    protected function renderContainerSections()
    {

    }

    public function renderNotificationText(NotificationInterface $notification)
    {
        $context = $this->getNotificationContext($notification);

        $replacements = [];
        foreach ($this->templateReplacements as $key => $item) {
            if (is_string($item)) {
                $replacements[$key] = ArrayHelper::getValue($context, $item);
            } else if (is_callable($item)) {
                $replacements[$key] = $item($notification, $this);
            }
        }

        $replacements['{timestamp}'] = Yii::$app->getFormatter()
            ->asDatetime($notification->getTimestamp(), $this->timestampFormat);

        return strtr($this->itemTemplate, $replacements);
    }

    protected function getNotificationContext(NotificationInterface $notification)
    {
        return [
            'text' => $notification->getCompiledText(),
            'notification' => $notification
        ];
    }

    protected function compileTemplateReplacements()
    {
        preg_match_all("/\{([^\}]+)\}/", $this->itemTemplate, $matches);

        $context = [
            'section' => $this->sections
        ];

        $this->templateReplacements = [];
        foreach ($matches[1] as $key) {
            $section = ArrayHelper::getValue($context, $key);

            if ($section !== null) {
                $this->templateReplacements["{{$key}}"] = $section;
            } else {
                $this->templateReplacements["{{$key}}"] = $key;
            }
        }
    }
}