<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;


interface TokenRetrievalInterface
{
    /**
     * Returns token for type.
     *
     * @param $type string Name of the target which is requesting token.
     * @param NotificationInterface $n Notification which needs token.
     */
    public function getToken(NotificationInterface $n);
}