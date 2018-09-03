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
     * Sets notification ID
     * @param $id int
     */
    public function setId($id);

    /**
     * Returns type of this notification.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets type of this notification.
     * @param $type string
     */
    public function setType($type);

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
     * Sets data for this notification. Which will be used when displaying notification info.
     *
     * @param $data array Data which will be set
     */
    public function setData($data);

    /**
     * Returns timestamp of this notification.
     *
     * @return string
     */
    public function getTimestamp();


    /**
     * Sets timestamp of this notification
     *
     * @param $timestamp int UNIX timestamp
     */
    public function setTimestamp($timestamp);

    /**
     * Returns whether or not notification is read.
     *
     * @return bool
     */
    public function isRead();

    /**
     * Sets if this notification is read.
     *
     * @param $isRead bool
     */
    public function setRead($isRead);


    /**
     * Returns user which owns this notification
     *
     * @return int
     */
    public function getUserId();

    /**
     * Sets user id.
     *
     * @param $userId int User ID
     */
    public function setUserId($userId);
}