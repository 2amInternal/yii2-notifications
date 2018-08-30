<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;

use dvamigos\Yii2\Notifications\exceptions\SaveFailedException;
use dvamigos\Yii2\Notifications\Notification;
use dvamigos\Yii2\Notifications\NotificationManager;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationTargetInterface;
use Yii;
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
     * @throws \yii\db\Exception
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

        $this->db->createCommand()->insert($this->notificationsTable, $data)->execute();
        $data['id'] = $this->db->getLastInsertID();

        return $this->createNotificationInstance($data);
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
        $this->markAsDeleted($id, $userId);
        return $this->create($type, $data, $userId);
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
        $this->db->createCommand()
            ->update($this->notificationsTable, [
                'is_read' => 1,
                'updated_at' => time()
            ], [
                'user_id' => $userId,
                'id' => $id
            ])
            ->execute();
    }

    /**
     * Marks notification as deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
     * @throws Exception
     */
    public function markAsDeleted($id, $userId)
    {
        $this->db->createCommand()
            ->update($this->notificationsTable, [
                'is_deleted' => 1,
                'updated_at' => time()
            ], [
                'user_id' => $userId,
                'id' => $id
            ])
            ->execute();
    }

    /**
     * Removes all notifications for specified user.
     *
     * @param $userId int User for which notifications will be cleared.
     *
     * @throws SaveFailedException Throws exception if storage could not save this notification.
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
     * @throws SaveFailedException Throws exception if storage could not save this notification.
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
        $items = (new Query())
            ->from($this->notificationsTable)
            ->where([
                'user_id' => $userId,
                'is_deleted' => 0,
            ])
            ->all($this->db);

        return array_map(function ($data) {
            return $this->createNotificationInstance($data);
        }, $items);
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
        $model = $this->findNotificationInstance($id, $userId);

        if (empty($model)) {
            throw new Exception(Yii::t('app', 'Notification ID to be replaced not found.'));
        }

        return $model;
    }

    /**
     * Find and returns notification instance or null if not found.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @return Notification|null
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    protected function findNotificationInstance($id, $userId)
    {
        $data = (new Query())
            ->from($this->notificationsTable)
            ->where([
                'id' => $id,
                'user_id' => $userId,
                'is_deleted' => 0
            ])
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

        $model->id = $data['id'];
        $model->type = $data['type'];
        $model->data = is_string($data['data']) ? Json::decode($data['data']) : $data['data'];
        $model->userId = $data['user_id'];
        $model->timestamp = $data['created_at'];
        $model->isRead = (bool)$data['is_read'];
        $model->owner = $this->owner;

        return $model;
    }
}