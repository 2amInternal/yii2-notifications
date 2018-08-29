<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\exceptions;


use dvamigos\Yii2\Notifications\NotificationStorageInterface;

class SaveFailedException extends BaseException
{
    public function __construct(NotificationStorageInterface $storage)
    {
        $message = "Could not save notification. Reason: " . implode(PHP_EOL, $storage->getFirstErrors());
        parent::__construct($message, 0, null);
    }
}