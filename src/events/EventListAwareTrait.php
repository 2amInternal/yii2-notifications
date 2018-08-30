<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\events;


use yii\base\Component;

/**
 * Trait EventListAwareTrait
 * @package dvamigos\Yii2\Notifications\invokeable
 *
 * @method on($eventName, $handler)
 * @see Component::on()
 */
trait EventListAwareTrait
{
    public function events()
    {
        return [];
    }

    public function init()
    {
        $this->attachEvents();
        parent::init();
    }

    public function attachEvents()
    {
        /** @var $this Component|EventListAwareTrait */
        $events = $this->events();

        foreach ($events as $eventName => $eventItems) {
            foreach ($eventItems as $eventHandler) {
                $this->on($eventName, $eventHandler);
            }
        }
    }
}