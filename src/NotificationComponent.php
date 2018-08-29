<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\di\Instance;
use yii\web\User;

class NotificationComponent extends Component
{
    /**
     * Translation category used for mapping translations.
     *
     * @var string
     */
    public $translationCategory = 'app';

    /**
     * Storage handler for notifications.
     *
     * @var NotificationStorageInterface|string
     */
    public $storage;

    /**
     * List of notification type groups which will be available for this notification.
     *
     * Type groups are grouped translation types
     *
     * Array should be in format:
     * [
     *      'typeName' => [
     *          'key1' => 'Translatable Notification text for key 1',
     *          'key2' => 'Translatable notification text for key 2'
     *      ]
     * ]
     *
     * It could also accept format:
     * [
     *      'typeName' => 'Translatable notification for typeName'
     * ]
     *
     * typeName - Type of the notification which will result in the notification text being shown.
     *
     * You can specify your own groups which will be used in templates. Below is an example of one:
     * [
     *      'new_user_created' => [
     *          'title' => 'New user {fullName} registered',
     *          'message' => 'New user {fullName} just registered on site.'
     *      ]
     * ]
     *
     * And to create this notification for current user you would use:
     * Yii::$app->notification->push('new_user_created', ['fullName' => 'John Doe']);
     *
     * This type name will be used in push notification.
     */
    public $types = [];

    /**
     * User component which will be used.
     *
     * @var string|User
     */
    public $user = 'user';


    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->user = Instance::ensure($this->user, User::class);
        $this->storage = Instance::ensure($this->storage, NotificationStorageInterface::class);
    }

    /**
     * Pushes new notification to user's list.
     *
     * @param $type string one of the types defined in $types
     * @param array $data string translation data which will be applied when the notification is rendered.
     * @param $userId integer|null For which user this will be applied. If null current user is used.
     * @return NotificationInterface
     * @throws Exception
     * @throws exceptions\SaveFailedException
     */
    public function push($type, $data = [], $userId = null)
    {
        $this->validateType($type);

        return $this->storage->create($type, $data, $this->resolveUserId($userId));
    }

    /**
     * Replaces old notification with newly defined notification.
     *
     * @param $id integer notification which will be replaced
     * @param $type string one of the types defined in $types
     * @param array $data string translation data which will be applied when the notification is rendered.
     * @param $userId integer|null For which user this will be applied. If null current user is used.
     * @return NotificationInterface
     * @throws exceptions\SaveFailedException
     */
    public function replace($id, $type, $data = [], $userId = null)
    {
        $userId = $this->resolveUserId($userId);

        /** @var NotificationInterface $model */
        $model = $this->storage->create($type, $data, $userId);

        $this->storage->replace($id, $model->getId(), $userId);

        return $model;
    }

    /**
     * Marks notification as read
     *
     * @param $id integer notification which will be marked
     * @return bool Whether or not operation is successful.
     * @throws exceptions\SaveFailedException
     */
    public function markAsRead($id, $userId = null)
    {
        return $this->storage->markAsDeleted($id, $this->resolveUserId($userId));
    }


    /**
     * Marks all notifications as read.
     *
     * @param int|null $userId User ID which will be used. Current User if null.
     * @return mixed
     * @throws exceptions\SaveFailedException
     */
    public function markAllRead($userId = null)
    {
        return $this->storage->markAllRead($this->resolveUserId($userId));
    }

    /**
     * Deletes notification
     *
     * @param $id integer notification which will be marked
     * @return bool Whether or not operation is successful.
     * @throws exceptions\SaveFailedException
     */
    public function delete($id, $userId = null)
    {
        return $this->storage->markAsDeleted($id, $this->resolveUserId($userId));
    }

    /**
     * Clears all notifications for one user.
     *
     * @param $userId integer|null For which user this will be applied. If null current user is used.
     * @return bool
     * @throws exceptions\SaveFailedException
     */
    public function clearAll($userId = null)
    {
        return $this->storage->clearAll($this->resolveUserId($userId));
    }

    /**
     * Returns array of notifications for this user in format.
     *
     * @return NotificationInterface[] List of notifications.
     */
    public function getNotifications($userId = null)
    {
        return $this->storage->findNotifications($userId);
    }

    /**
     * Resolves text of the notification based on the type.
     *
     * @param $notification NotificationInterface Notification which will be used to get text.
     *
     * @return string Resolved text.
     * @throws Exception
     */
    public function getNotificationText($notification)
    {
        return $this->getText($notification->getType(), $notification->getData());
    }

    /**
     * Resolves text of the notification based on the type.
     *
     * @param $type string Type defined in $types of this component.
     * @param array $data Data which will be used in Yii::t() of resolved text.
     * @return string Resolved text.
     *
     * @throws Exception
     */
    public function getText($type, $data = [])
    {
        $this->validateType($type);

        $typeData = $this->types[$type];

        if (is_string($typeData)) {
            return Yii::t($this->translationCategory, $typeData, $data);
        }

        foreach ($typeData as $key => $text) {
            $typeData[$key] = Yii::t($this->translationCategory, $text, $data);
        }

        return $typeData;
    }

    /**
     * Resolves user id to current user if user ID is null.
     *
     * @param $userId int User ID which will be resolved.
     * @return int|string User ID of the current used or current user ID.
     */
    protected function resolveUserId($userId)
    {
        return $userId ?? $this->user->getId();
    }

    /**
     * Validates type of the notification.
     *
     * @param $type string Type which will be validated.
     * @throws Exception
     */
    protected function validateType($type)
    {
        if (!empty($this->types[$type])) {
            return;
        }

        throw new Exception(Yii::t(
            $this->translationCategory,
            "This type '{type}' is not defined! Please check your configuration.", [
            'type' => $type
        ]));
    }
}