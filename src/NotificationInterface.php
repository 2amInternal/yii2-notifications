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
     * Returns notification by ID.
     *
     * @param $id int ID of the notification
     * @param $userId int User ID owner of the notification.
     * @return NotificationInterface|null
     */
    public static function findForUser($id, $userId);

    /**
     * Sets notification owner component
     *
     * @param NotificationComponent $owner
     */
    public function setOwner(NotificationComponent $owner);

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
     * Returns array of first errors for which this storage could not save changes.
     *
     * @return array List of strings describing the errors.
     */
    public function getFirstErrors();

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
     * Marks notification as read.
     */
    public function markAsRead();


    /**
     * Marks notification as deleted.
     */
    public function markAsDeleted();

    /**
     * Returns whether or not notification is read.
     *
     * @return bool
     */
    public function isRead();
}