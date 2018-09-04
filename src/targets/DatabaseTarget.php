<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;

use dvamigos\Yii2\Notifications\exceptions\NotificationNotFoundException;
use dvamigos\Yii2\Notifications\Notification;
use dvamigos\Yii2\Notifications\NotificationManager;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationTargetInterface;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\Json;

class DatabaseTarget extends BaseObject implements NotificationTargetInterface
{
    /** @var string|Notification */
    public $dataClass = Notification::class;

    /**
     * Database connection used for storage.
     *
     * @var string|Connection
     */
    public $db = 'db';

    /**
     * Notification Table
     *
     * @var string
     */
    public $notificationsTable = '{{notification}}';

    /**
     * Order of the notifications when returning all notifications.
     *
     * @var array
     */
    public $notificationOrder = ['created_at' => SORT_DESC];

    /** @var NotificationManager */
    protected $owner;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * Sets storage owner component.
     *
     * @param NotificationManager $owner
     */
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
     * @throws \yii\base\InvalidConfigException
     *
     * @return NotificationInterface Instance of this notification.
     */
    public function create($type, $data, $userId)
    {
        $data = [
            'type' => $type,
            'data' => Json::encode($data),
            'user_id' => $userId,
            'created_at' => time(),
            'is_read' => 0
        ];

        $notification = $this->createNotificationInstance($data);
        $this->saveNotification($notification);
        return $notification;
    }


    /**
     * Updates existing notification in the storage for specified user.
     *
     * @param $id int Notification ID
     * @param $type string Notification type
     * @param $data array Additional data for this notification.
     * @param $userId int User ID for which this notification relates to.
     *
     * @throws Exception
     *
     * @return NotificationInterface Instance of new notification.
     */
    public function update($id, $type, $data, $userId)
    {
        $notification = $this->findNotification($id, $userId);

        if (empty($notification)) {
            throw new NotificationNotFoundException($id, $userId);
        }

        $notification->setType($type);
        $notification->setData($data);
        $this->saveNotification($notification);

        return $notification;
    }

    /**
     * Marks notification as read.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     * @throws Exception
     */
    public function markAsRead($id, $userId)
    {
        $this->markRead($id, $userId, 1);
    }

    /**
     * Marks notification as deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     * @throws Exception
     */
    public function markAsDeleted($id, $userId)
    {
        $this->markDeleted($id, $userId, 1);
    }

    /**
     * Marks notification as unread.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     * @throws \yii\db\Exception
     */
    public function markAsUnread($id, $userId)
    {
        $this->markRead($id, $userId, 0);
    }

    /**
     * Marks notification as not deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     * @throws \yii\db\Exception
     */
    public function markAsNotDeleted($id, $userId)
    {
        $this->markDeleted($id, $userId, 0);
    }

    /**
     * Removes all notifications for specified user.
     *
     * @param $userId int User for which notifications will be cleared.
     *
     * @throws \yii\db\Exception
     */
    public function clearAll($userId)
    {
        $this->db->createCommand()
            ->update($this->notificationsTable, [
                'is_deleted' => 1,
                'updated_at' => time()
            ], [
                'user_id' => $userId,
                'is_deleted' => 0
            ])
            ->execute();
    }

    /**
     * Marks all notifications as read for specified user.
     *
     * @param $userId int User for which notifications will be marked as read.
     * @throws \yii\db\Exception
     */
    public function markAllRead($userId)
    {
        $this->db->createCommand()
            ->update($this->notificationsTable, [
                'is_read' => 1
            ], [
                'user_id' => $userId,
                'is_read' => 0
            ])
            ->execute();
    }

    /**
     * Returns list of notifications
     *
     * @param $userId int User for which notifications will be returned.
     * @return NotificationInterface[] List of notifications
     */
    public function findNotifications($userId)
    {
        return array_map(function ($data) {

            return $this->createNotificationInstance($data);

        }, $this->getAllNotificationsQuery($userId)->all($this->db));
    }

    /**
     * Finds one notification.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @return Notification|null
     * @throws \yii\base\InvalidConfigException
     */
    public function findNotification($id, $userId)
    {
        $model = $this->findNotificationInstance($id, $userId);

        return $model;
    }

    /**
     * Find and returns notification instance or null if not found.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @return Notification|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function findNotificationInstance($id, $userId)
    {
        $data = $this->getOneNotificationQuery($id, $userId)
            ->one($this->db);

        return $this->createNotificationInstance($data);
    }

    /**
     * Creates and returns notification instance from data.
     *
     * @param $data array|bool If data is false then result is null
     * @return Notification|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function createNotificationInstance($data)
    {
        if ($data === false) {
            return null;
        }

        /** @var Notification $model */
        $model = Instance::ensure($this->dataClass, Notification::class);

        if (!empty($data['id'])) {
            $model->setId($data['id']);
        }

        $model->setType($data['type']);
        $model->setData(is_string($data['data']) ? Json::decode($data['data']) : $data['data']);
        $model->setUserId($data['user_id']);
        $model->setTimestamp($data['created_at']);
        $model->setRead((bool)$data['is_read']);
        $model->setOwner($this->owner);

        return $model;
    }

    protected function saveNotification(Notification $notification)
    {
        $row = [
            'type' => $notification->getType(),
            'data' => Json::encode($notification->getData()),
            'user_id' => $notification->getUserId(),
            'created_at' => $notification->getTimestamp(),
            'is_read' => (int)$notification->isRead()
        ];

        $db = $this->db->createCommand();

        if ($notification->getId() !== null) {
            $db->update($this->notificationsTable, $row, ['id' => $notification->getId()]);
        } else {
            $db->insert($this->notificationsTable, $row);
        }

        $db->execute();
        $notification->setId($this->db->getLastInsertID());
    }

    /**
     * Returns one notification query.
     *
     * @param $id int Notification ID
     * @param $userId int User ID which will be used to ensure that notification belongs to that user.
     *
     * @return Query
     */
    protected function getOneNotificationQuery($id, $userId)
    {
        return $this->getAllNotificationsQuery($userId)->andWhere([
            'id' => $id
        ]);
    }

    /**
     * Returns all notifications query
     *
     * @param $userId int User ID which will be used as filter.
     *
     * @return Query
     */
    protected function getAllNotificationsQuery($userId)
    {
        return (new Query())
            ->from($this->notificationsTable)
            ->where([
                'user_id' => $userId,
                'is_deleted' => 0,
            ])
            ->orderBy($this->notificationOrder);
    }

    /**
     * Mark notification as read or unread.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @param $isRead
     * @throws \yii\db\Exception
     */
    protected function markRead($id, $userId, $isRead)
    {
        $this->db->createCommand()
            ->update($this->notificationsTable, [
                'is_read' => $isRead,
                'updated_at' => time()
            ], [
                'user_id' => $userId,
                'id' => $id
            ])
            ->execute();
    }

    /**
     * Mark notification as deleted or not deleted.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @param $isDeleted int Is deleted
     *
     * @throws \yii\db\Exception
     */
    protected function markDeleted($id, $userId, $isDeleted)
    {
        $this->db->createCommand()
            ->update($this->notificationsTable, [
                'is_deleted' => $isDeleted,
                'updated_at' => time()
            ], [
                'user_id' => $userId,
                'id' => $id
            ])
            ->execute();
    }
}