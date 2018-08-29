<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\storage;


use dvamigos\Yii2\Notifications\exceptions\SaveFailedException;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationStorageInterface;
use yii\base\BaseObject;

class DatabaseStorage extends BaseObject implements NotificationStorageInterface
{
    public $tableName = '';

    /**
     * Creates new notification in the storage for specified user.
     *
     * @param $type string Notification type
     * @param $data array Additional data for this notification.
     * @param $userId int User ID for which this notification relates to.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     *
     * @return NotificationInterface Instance of this notification.
     */
    public function create($type, $data, $userId)
    {
        // TODO: Implement create() method.
    }

    /**
     * Replaces notification ID with new notification ID.
     *
     * @param $id int ID of the notification to be replaced.
     * @param $withId int ID of the notification which replaces this notification.
     * @param $userId int User to be used.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     */
    public function replace($id, $withId, $userId)
    {
        // TODO: Implement replace() method.
    }

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
    public function markAsRead($id, $userId)
    {
        // TODO: Implement markAsRead() method.
    }

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
    public function markAsDeleted($id, $userId)
    {
        // TODO: Implement markAsDeleted() method.
    }

    /**
     * Removes all notifications for specified user.
     *
     * @param $userId int User for which notifications will be cleared.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     */
    public function clearAll($userId)
    {
        // TODO: Implement clearAll() method.
    }

    /**
     * Marks all notifications as read for specified user.
     *
     * @param $userId int User for which notifications will be marked as read.
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     *
     * @return mixed
     */
    public function markAllRead($userId)
    {
        // TODO: Implement markAllRead() method.
    }

    /**
     * Returns list of notifications
     *
     * @param $userId int User for which notifications will be returned.
     * @return NotificationInterface[] List of notifications
     */
    public function findNotifications($userId)
    {
        // TODO: Implement findNotifications() method.
    }
}