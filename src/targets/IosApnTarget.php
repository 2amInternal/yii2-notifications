<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;


use dvamigos\Yii2\Notifications\IosNotification;
use dvamigos\Yii2\Notifications\exceptions\NotificationNotFoundException;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationManager;
use dvamigos\Yii2\Notifications\NotificationTargetInterface;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\di\Instance;
use yii\helpers\Json;


/**
 * Class IosApnTarget
 * @package dvamigos\Yii2\Notifications\targets
 *
 * NOTE: This class is not considered stable yet. Use it at your own risk.
 */
class IosApnTarget extends BaseObject implements NotificationTargetInterface
{
    /** @var string|IosNotification */
    public $dataClass = IosNotification::class;

    /**
     * Send APN request in sandbox mode.
     *
     * @var bool
     */
    public $sandbox = false;

    /**
     * Password for APN
     *
     * @var string
     */
    public $password;

    /**
     * Pem key file for APN
     *
     * @var string
     */
    public $pemFile;

    /** @var NotificationManager */
    protected $owner;

    /**
     * Connection socket.
     *
     * @var resource|null
     */
    protected $socket = null;

    public function init()
    {
        parent::init();

        register_shutdown_function(function () {
            $this->closeConnection();
        });
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
     *
     * @return NotificationInterface Instance of new notification.
     */
    public function create($type, $data, $userId)
    {
        $notification = $this->createNotificationInstance($type, $data, $userId);
        $this->sendNotification($notification);
        return $notification;
    }

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
    public function update($id, $type, $data, $userId)
    {
        return null;
    }

    /**
     * Marks notification as read.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     */
    public function markAsRead($id, $userId)
    {
    }

    /**
     * Marks notification as unread.
     *
     * @param $id int ID of the notification which will be marked as read.
     * @param $userId int User to be used.
     *
     */
    public function markAsUnread($id, $userId)
    {
    }

    /**
     * Marks notification as deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     */
    public function markAsDeleted($id, $userId)
    {
    }

    /**
     * Marks notification as not deleted.
     *
     * @param $id int ID of the notification to be marked.
     * @param $userId int User to be used. If null it refers to current user.
     *
     */
    public function markAsNotDeleted($id, $userId)
    {
    }

    /**
     * Removes all notifications for specified user.
     *
     * @param $userId int User for which notifications will be cleared.
     *
     */
    public function clearAll($userId)
    {
    }

    /**
     * Marks all notifications as read for specified user.
     *
     * @param $userId int User for which notifications will be marked as read.
     */
    public function markAllRead($userId)
    {
    }

    /**
     * Returns list of notifications
     *
     * @param $userId int User for which notifications will be returned.
     * @return NotificationInterface[] List of notifications
     */
    public function findNotifications($userId)
    {
        return [];
    }

    /**
     * Finds one notification.
     *
     * @param $id int Notification ID
     * @param $userId int Notification User ID
     * @return NotificationInterface
     * @throws NotificationNotFoundException
     */
    public function findNotification($id, $userId)
    {
        return null;
    }

    protected function sendNotification(IosNotification $notification)
    {
        if (!$this->socket) {
            $this->openConnection();
        }

        return (bool)($this->sendPayload($notification));
    }

    protected function getApiUrl()
    {
        if ($this->sandbox) {
            return 'ssl://gateway.sandbox.push.apple.com:2195';
        }

        return 'ssl://gateway.push.apple.com:2195';
    }

    protected function createNotificationInstance($type, $data, $userId)
    {
        /** @var IosNotification $notification */
        $notification = Instance::ensure($this->dataClass, IosNotification::class);

        $notification->setId(0);
        $notification->setOwner($this->owner);
        $notification->setType($type);
        $notification->setData($data);
        $notification->setTimestamp(time());
        $notification->setRead(false);
        $notification->setUserId($userId);

        return $notification;
    }

    protected function closeConnection()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    protected function openConnection()
    {
        if ($this->socket) {
            $this->closeConnection();
        }

        $context = stream_context_create();

        stream_context_set_option($context, 'ssl', 'local_cert', Yii::getAlias($this->pemFile));
        stream_context_set_option($context, 'ssl', 'passphrase', $this->password);

        $socket = stream_socket_client(
            $this->getApiUrl(),
            $errorCode,
            $errorString,
            60,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT,
            $context
        );

        if (!is_resource($socket)) {
            throw new Exception('Cannot connect to: ' . $this->getApiUrl());
        }

        return $socket;
    }

    protected function sendPayload(IosNotification $notification)
    {
        $payload = Json::encode($notification->getBody());
        $token = $notification->getNotificationToken();

        $binaryMessage = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;

        return fwrite($this->socket, $binaryMessage, strlen($binaryMessage));
    }
}