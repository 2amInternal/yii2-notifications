<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;

use dvamigos\Yii2\Notifications\exceptions\SaveFailedException;
use dvamigos\Yii2\Notifications\models\Notification;
use dvamigos\Yii2\Notifications\NotificationManager;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationTargetInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;

class DatabaseTarget extends BaseObject implements NotificationTargetInterface
{
    /** @var string|Notification */
    public $storageClass = Notification::class;

    /** @var NotificationManager */
    protected $owner;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
    }

    public function setOwner(NotificationManager $owner)
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

        /** @var Notification $model */
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
     * @return Notification
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
        /** @var Notification $storageClass */
        $storageClass = $this->storageClass;

        /** @var Notification $model */
        $model = $storageClass::findForUser($id, $userId);
        $model->setOwner($this->owner);

        return $model;
    }
}