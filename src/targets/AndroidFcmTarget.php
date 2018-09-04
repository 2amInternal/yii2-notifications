<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;


use dvamigos\Yii2\Notifications\FcmNotification;
use dvamigos\Yii2\Notifications\exceptions\NotificationNotFoundException;
use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\NotificationManager;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class AndroidFcmTarget extends ApiClientTarget
{
    /** @var string|FcmNotification */
    public $dataClass = FcmNotification::class;

    /** @var NotificationManager */
    protected $owner;

    /**
     * API key for Google FCM
     *
     * @var string
     */
    public $apiKey;

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

    public function getBaseApiUrl()
    {
        return "https://fcm.googleapis.com/fcm";
    }

    protected function clientOptions()
    {
        return [
            'headers' => [
                "Authorization" => "key={$this->apiKey}",
                "Content-Type" => "application/json"
            ]
        ];
    }

    protected function sendNotification(FcmNotification $notification)
    {
        $response = $this->sendRequest("POST", "send", [
            'json' => $notification->getFcmRequestData()
        ]);

        $result = Json::decode($response->getBody()->getContents());

        return ArrayHelper::getValue($result, 'success', 0) == 1;
    }

    protected function createNotificationInstance($type, $data, $userId)
    {
        /** @var FcmNotification $notification */
        $notification = Instance::ensure($this->dataClass, FcmNotification::class);

        $notification->setId(0);
        $notification->setOwner($this->owner);
        $notification->setType($type);
        $notification->setData($data);
        $notification->setTimestamp(time());
        $notification->setRead(false);
        $notification->setUserId($userId);

        return $notification;
    }
}