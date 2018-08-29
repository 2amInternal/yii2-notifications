<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\storage;


use dvamigos\Yii2\Notifications\exceptions\SaveFailedException;
use dvamigos\Yii2\Notifications\models\Notifications;
use dvamigos\Yii2\Notifications\NotificationComponent;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationStorageInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;

class DatabaseStorage extends BaseObject implements NotificationStorageInterface
{
    /** @var string|Notifications */
    public $storageClass = Notifications::class;

    /** @var NotificationComponent */
    protected $owner;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
    }

    public function setOwner(NotificationComponent $owner)
    {
        $this->owner = $owner;
    }

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
        $storageClass = $this->storageClass;

        /** @var Notifications $model */
        $model = new $storageClass();

        $model->setType($type);
        $model->setData($data);

        $model->created_by = $userId;

        $model->setOwner($this->owner);

        $model->persist();

        return $model;
    }

    /**
     * Replaces notification ID with new notification ID.
     *
     * @param $id int ID of the notification to be replaced.
     * @param $type string Notification type
     * @param $data array Additional data for this notification.
     * @param $userId int User ID for which this notification relates to.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     * @throws Exception
     *
     * @return NotificationInterface
     */
    public function replace($id, $type, $data, $userId)
    {
        $replacement = $this->findNotification($id, $userId);
        $replacement->markAsDeleted();

        return $this->create($type, $data, $userId);
    }

    /**
     * Marks notification as read.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     * @throws Exception
     *
     * @return bool Whether or not notification is marked as read. False if it was already.
     */
    public function markAsRead($id, $userId)
    {
        $notification = $this->findNotification($id, $userId);

        if ($notification->isRead()) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * Marks notification as deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     * @throws Exception
     *
     * @return bool Whether or not notification is marked as deleted. False if it was already.
     */
    public function markAsDeleted($id, $userId)
    {
        $notification = $this->findNotificationModel($id, $userId);

        if (empty($notification)) {
            return false;
        }

        $notification->markAsDeleted();
        return true;
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
        $storageClass = $this->storageClass;
        $storageClass::deleteAllForUser($userId);
    }

    /**
     * Marks all notifications as read for specified user.
     *
     * @param $userId int User for which notifications will be marked as read.
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     */
    public function markAllRead($userId)
    {
        $storageClass = $this->storageClass;
        $storageClass::readAllForUser($userId);
    }

    /**
     * Returns list of notifications
     *
     * @param $userId int User for which notifications will be returned.
     * @return NotificationInterface[] List of notifications
     */
    public function findNotifications($userId)
    {
        $storageClass = $this->storageClass;

        return array_map(function(NotificationInterface $notification) {
            $notification->setOwner($this->owner);
        }, $storageClass::findAllForUser($userId));
    }

    /**
     * Finds one notification.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @return Notifications
     * @throws Exception
     */
    protected function findNotification($id, $userId)
    {
        $model = $this->findNotificationModel($id, $userId);

        if (empty($model)) {
            throw new Exception(Yii::t('app', 'Notification ID to be replaced not found.'));
        }

        return $model;
    }

    protected function findNotificationModel($id, $userId)
    {
        /** @var Notifications $storageClass */
        $storageClass = $this->storageClass;

        /** @var Notifications $model */
        $model = $storageClass::findForUser($id, $userId);
        $model->setOwner($this->owner);

        return $model;
    }
}