<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;

use dvamigos\Yii2\Notifications\exceptions\TargetStackEmptyException;
use dvamigos\Yii2\Notifications\targets\DatabaseTarget;
use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\di\Instance;
use yii\web\User;

class NotificationManager extends Component
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
     * Format is in:
     *
     * [
     *     'database' => DatabaseTarget::class
     * ]
     *
     * or:
     *
     * [
     *     'database' => [
     *          'class' => DatabaseTarget::class,
     *          'config1' => 'value'
     *     ]
     * ]
     *
     * @var NotificationTargetInterface|string
     */
    public $targets = [
        'database' => DatabaseTarget::class
    ];

    /**
     * Default target for notification
     *
     * If target is string then one target is used and every function will return result directly
     * from that target.
     *
     * If multiple targets are used then everything is executed on multiple targets and results
     * are serialized in associative array.
     *
     * @see NotificationManager::callTarget()
     *
     * @var string|array
     */
    public $target = 'database';

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
     * Target instances
     *
     * @var array
     */
    protected $targetObjects = [];

    /**
     * Target names stack
     *
     * @var array
     */
    protected $targetNameStack = [];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->user = Instance::ensure($this->user, User::class);
    }

    /**
     * Sets new target as the current target and stores the old target state.
     *
     * @param $newTarget string|array New target.
     */
    public function pushTarget($newTarget)
    {
        $this->targetNameStack[] = $this->target;
        $this->target = $newTarget;
    }

    /**
     * Restores old target from the stack.
     *
     * @throws TargetStackEmptyException
     */
    public function popTarget()
    {
        if (empty($this->targetNameStack)) {
            throw new TargetStackEmptyException();
        }

        $this->target = array_pop($this->targetNameStack);
    }

    /**
     * Pushes new targets to the stack and executes callable
     *
     * @param $targets string|array Targets which will be pushed.
     * @param $callable callable Callable which will be executed.
     * @throws TargetStackEmptyException
     */
    public function forTargets($targets, $callable)
    {
        $this->pushTarget($targets);
        $callable($this);
        $this->popTarget();
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
        return $this->callTarget('create', [$type, $data, $this->resolveUserId($userId)]);
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
     * @throws \yii\base\InvalidConfigException
     * @throws Exception
     */
    public function replace($id, $type, $data = [], $userId = null)
    {
        $this->validateType($type);
        return $this->callTarget('replace', [$id, $type, $data, $this->resolveUserId($userId)]);
    }

    /**
     * Marks notification as read
     *
     * @param $id integer notification which will be marked
     * @return bool Whether or not operation is successful.
     * @throws exceptions\SaveFailedException
     * @throws \yii\base\InvalidConfigException
     */
    public function markAsRead($id, $userId = null)
    {
        return $this->callTarget('markAsDeleted', [$id, $this->resolveUserId($userId)]);
    }


    /**
     * Marks all notifications as read.
     *
     * @param int|null $userId User ID which will be used. Current User if null.
     * @return mixed
     * @throws exceptions\SaveFailedException
     * @throws \yii\base\InvalidConfigException
     */
    public function markAllRead($userId = null)
    {
        return $this->callTarget('markAllRead', [$this->resolveUserId($userId)]);
    }

    /**
     * Deletes notification
     *
     * @param $id integer notification which will be marked
     * @return bool Whether or not operation is successful.
     * @throws exceptions\SaveFailedException
     * @throws \yii\base\InvalidConfigException
     */
    public function delete($id, $userId = null)
    {
        return $this->callTarget('markAsDeleted', [$id, $this->resolveUserId($userId)]);
    }

    /**
     * Clears all notifications for one user.
     *
     * @param $userId integer|null For which user this will be applied. If null current user is used.
     * @return bool
     * @throws exceptions\SaveFailedException
     * @throws \yii\base\InvalidConfigException
     */
    public function clearAll($userId = null)
    {
        return $this->callTarget('clearAll', [$this->resolveUserId($userId)]);
    }

    /**
     * Returns array of notifications for this user in format.
     *
     * @return NotificationInterface[] List of notifications.
     * @throws \yii\base\InvalidConfigException
     */
    public function getNotifications($userId = null)
    {
        return $this->callTarget('findNotifications', [$this->resolveUserId($userId)]);
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
    public function compileText($type, $data = [])
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

    /**
     * Executes method on one or more targets and returns the result.
     *
     * Result will be returned based on specified $target.
     *
     * If target is a string then only result from that target is returned directly.
     * If target is an array then array is returned in format:
     * [
     *    'targetName' => 'result'
     * ]
     *
     * @param $method string method to be executed.
     * @param $params array params to be passed.
     * @throws \yii\base\InvalidConfigException
     *
     * @return mixed
     */
    protected function callTarget($method, $params)
    {
        $targets = [];

        if (is_string($this->target)) {
            $targets[] = $this->getTarget($this->target);
        } elseif (is_array($this->target)) {
            foreach ($this->target as $name) {
                $targets[] = $this->getTarget($name);
            }
        }

        $results = [];

        foreach ($targets as $target) {
            $results[$target] = call_user_func_array([$target, $method], $params);
        }

        if (is_string($this->target)) {
            return $results[$this->target];
        }

        return $results;
    }

    /**
     * Returns notification target.
     *
     * @param $targetName string target name
     * @return NotificationTargetInterface
     * @throws \yii\base\InvalidConfigException
     */
    protected function getTarget($targetName)
    {
        if (empty($this->targetObjects[$targetName])) {
            $this->targetObjects[$targetName] = Instance::ensure($this->targets[$targetName], NotificationTargetInterface::class);
        }

        return $this->targetObjects[$targetName];
    }
}