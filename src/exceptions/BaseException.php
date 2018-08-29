<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\exceptions;


use yii\base\Exception;

class BaseException extends Exception
{
    public function getName()
    {
        try {
            return (new \ReflectionClass(static::class))->getShortName();
        } catch (\ReflectionException $e) {
            return "Exception";
        }
    }
}