<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;


use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class TextRenderer extends BaseObject
{
    /**
     * Item template
     *
     * Allowed string:
     * {text} - only available when right side of notification type in manager is string
     * {text.key} - only available when right side of the notification type in manager is associative array.
     *              key represents the key of that array.
     * {section.key} - renders a section from $sections list. Where key is section name.
     * {this.key} - renders a value using this compiler property or getter function.
     * {notification.key} - renders a value from notification object directly. Key represents parameter from notification object.
     *
     *
     * @see TextRenderer::$sections
     * @var string|callable
     */
    protected $template = "{text}";

    /**
     * Rendering sections
     *
     * Should be in format:
     * [
     *     'key' => function(NotificationInterface $item) {
     *          return 'result';
     *     }
     * ]
     *
     * @var array
     */
    public $sections = [];

    /**
     * Strings which will be replaced directly.
     *
     * @var array
     */
    public $stringReplacements = [];

    protected $replacements = [];

    public function setTemplate($template)
    {
        $this->template = $template;
        $this->compileTemplateReplacements();
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function render(NotificationInterface $n)
    {
        $replacements = $this->getTemplateReplacements($n);

        foreach ($this->stringReplacements as $key => $item) {
            $replacements[$key] = $item;
        }

        return strtr($this->template, $replacements);
    }

    /**
     * Compile template replacements for use when rendering single notification
     */
    protected function compileTemplateReplacements()
    {
        preg_match_all("/\{([^\}]+)\}/", $this->template, $matches);

        $this->replacements = [];
        foreach ($matches[1] as $key) {
            if (stripos($key, "section.") !== false) {
                $this->replacements["{{$key}}"] = ['section', substr($key, strlen("section."))];
            } else {
                $this->replacements["{{$key}}"] = ['text', $key];
            }
        }
    }

    /**
     * Returns context for rendering information about notification.
     *
     * @param NotificationInterface $notification
     * @return array
     */
    protected function getContext(NotificationInterface $notification)
    {
        return [
            'text' => $notification->getCompiledText(),
            'notification' => $notification,
            'this' => $this
        ];
    }

    protected function getTemplateReplacements(NotificationInterface $n)
    {
        $context = $this->getContext($n);

        $replacements = [];
        foreach ($this->replacements as $key => $item) {
            $replacements[$key] = $this->resolveReplacementValue($context, $item);
        }

        return $replacements;
    }

    protected function resolveReplacementValue($context, array $item)
    {
        list($type, $value) = $item;

        if ($type === "section") {
            return $this->sections[$value]($context, $this);
        }

        if ($type === "text") {
            return ArrayHelper::getValue($context, $item);
        }

        throw new Exception(Yii::t('app', 'Unknown type: {type}', ['type' => $type]));
    }
}