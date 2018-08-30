<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

use dvamigos\Yii2\Notifications\exceptions\SaveFailedException;

interface NotificationTargetInterface
{
    /**
     * Sets storage owner component.
     *
     * @param NotificationManager $owner
     */
    public function setOwner(NotificationManager $owner);

    /**
     * Creates new notification in the storage for specified user.
     *
     * @param $type string Notification type
     * @param $data array Additional data for this notification.
     * @param $userId int User ID for which this notification relates to.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     *
     * @return NotificationInterface Instance of new notification.
     */
    public function create($type, $data, $userId);

    /**
     * Replaces notification ID with new notification ID.
     *
     * @param $id int ID of the notification to be replaced.
     * @param $type string Notification type
     * @param $data array Additional data for this notification.
     * @param $userId int User ID for which this notification relates to.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     *
     * @return NotificationInterface Instance of new notification.
     */
    public function replace($id, $type, $data, $userId);


    /**
     * Marks notification as read.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     *
     * @return bool Whether or not notification is marked as read. False if it was already.
     */
    public function markAsRead($id, $userId);

    /**
     * Marks notification as deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     *
     * @return bool Whether or not notification is marked as deleted. False if it was already.
     */
    public function markAsDeleted($id, $userId);

    /**
     * Removes all notifications for specified user.
     *
     * @param $userId int User for which notifications will be cleared.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     */
    public function clearAll($userId);

    /**
     * Marks all notifications as read for specified user.
     *
     * @param $userId int User for which notifications will be marked as read.
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     */
    public function markAllRead($userId);

    /**
     * Returns list of notifications
     *
     * @param $userId int User for which notifications will be returned.
     * @return NotificationInterface[] List of notifications
     */
    public function findNotifications($userId);
}