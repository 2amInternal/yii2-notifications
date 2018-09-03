<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

use dvamigos\Yii2\Notifications\exceptions\NotificationNotFoundException;

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
     *
     * @return NotificationInterface Instance of new notification.
     */
    public function create($type, $data, $userId);

    /**
     * Updates existing notification in the storage for specified user.
     *
     * @param $id int
     * @param $type string Notification type
     * @param $data array Additional data for this notification.
     * @param $userId int User ID for which this notification relates to.
     *
     * @return NotificationInterface Instance of new notification.
     */
    public function update($id, $type, $data, $userId);


    /**
     * Marks notification as read.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     */
    public function markAsRead($id, $userId);

    /**
     * Marks notification as unread.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     */
    public function markAsUnread($id, $userId);

    /**
     * Marks notification as deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     */
    public function markAsDeleted($id, $userId);

    /**
     * Marks notification as not deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     */
    public function markAsNotDeleted($id, $userId);

    /**
     * Removes all notifications for specified user.
     *
     * @param $userId int User for which notifications will be cleared.
     *
     */
    public function clearAll($userId);

    /**
     * Marks all notifications as read for specified user.
     *
     * @param $userId int User for which notifications will be marked as read.
     */
    public function markAllRead($userId);

    /**
     * Returns list of notifications
     *
     * @param $userId int User for which notifications will be returned.
     * @return NotificationInterface[] List of notifications
     */
    public function findNotifications($userId);

    /**
     * Finds one notification.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @return NotificationInterface
     * @throws NotificationNotFoundException
     */
    public function findNotification($id, $userId);
}