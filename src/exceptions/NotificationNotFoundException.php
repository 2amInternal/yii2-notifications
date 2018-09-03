<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\exceptions;


use Yii;

class NotificationNotFoundException extends BaseException
{
    public function __construct($notificationId, $userId)
    {
        parent::__construct(Yii::t('app', 'Notification ID: {id} not found for user ID: {userId}.', [
            'id' => $notificationId,
            'userId' => $userId
        ]));
    }
}