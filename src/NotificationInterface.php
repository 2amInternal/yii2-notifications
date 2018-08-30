<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

interface NotificationInterface
{
    /**
     * Sets notification owner component
     *
     * @param NotificationManager $owner
     */
    public function setOwner(NotificationManager $owner);

    /**
     * Returns compiled notification text.
     *
     * @return array|string
     */
    public function getCompiledText();

    /**
     * Returns notification ID
     *
     * @return int
     */
    public function getId();

    /**
     * Returns type of this notification.
     *
     * @return string
     */
    public function getType();

    /**
     * Returns data for this notification. Which will be used when displaying notification info.
     *
     * @return array Data which can be passed in Yii::t().
     *
     * @see Yii::t()
     *
     */
    public function getData();

    /**
     * Returns timestamp of this notification.
     *
     * @return string
     */
    public function getTimestamp();

    /**
     * Returns whether or not notification is read.
     *
     * @return bool
     */
    public function isRead();


    /**
     * Returns user which owns this notification
     *
     * @return int
     */
    public function getUserId();
}