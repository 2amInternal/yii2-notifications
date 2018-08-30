<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\models;

use dvamigos\Yii2\Notifications\exceptions\SaveFailedException;
use dvamigos\Yii2\Notifications\NotificationManager;
use dvamigos\Yii2\Notifications\NotificationInterface;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;

/**
 * Class NotificationModel
 *
 * @property string $id
 * @property string $type
 * @property string $data
 * @property integer $is_read
 * @property integer $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string $created_by
 * @property string $updated_by
 */
class Notification extends \yii\db\ActiveRecord implements NotificationInterface
{
    /**
     * @var NotificationManager
     */
    protected $owner;

    public static function tableName()
    {
        return '{{%notification}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,
            'blameable' => BlameableBehavior::class
        ];
    }

    /**
     * Returns notification ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns type of this notification.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns data for this notification. Which will be used when displaying notification info.
     *
     * @return array Data which can be passed in Yii::t().
     *
     * @see Yii::t()
     *
     */
    public function getData()
    {
        return Json::decode($this->data);
    }

    /**
     * Returns timestamp of this notification.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->created_at;
    }

    /**
     * Sets type of this notification.
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Sets data for this notification. Which will be used when displaying notification info.
     */
    public function setData($data)
    {
        $this->data = Json::encode($data);
    }

    /**
     * Saves data into the storage medium.
     * @throws SaveFailedException
     */
    public function persist()
    {
        if (!$this->save()) {
            throw new SaveFailedException($this);
        }
    }

    /**
     * Returns notification by ID.
     *
     * @param $id int ID of the notification
     * @param $userId int User ID owner of the notification.
     * @return NotificationInterface|null
     */
    public static function findForUser($id, $userId)
    {
        return static::findOne([
            'id' => $id,
            'created_by' => $userId
        ]);
    }

    /**
     * Marks notification as read.
     * @throws SaveFailedException
     */
    public function markAsRead()
    {
        $this->is_read = 1;
        $this->persist();
    }

    /**
     * Marks notification as deleted.
     * @throws SaveFailedException
     */
    public function markAsDeleted()
    {
        $this->is_deleted = 1;
        $this->persist();
    }

    /**
     * Returns whether or not notification is read.
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->is_read == 1;
    }

    public static function deleteAllForUser($userId)
    {
        return static::deleteAll(['created_by' => $userId]);
    }

    public static function readAllForUser($userId)
    {
        return static::updateAll(['is_read' => 1], ['created_by' => $userId]);
    }

    /** @return static[] */
    public static function findAllForUser($userId)
    {
        return static::findAll(['created_by' => $userId]);
    }

    /**
     * Sets notification owner component
     *
     * @param Notification $owner
     */
    public function setOwner(NotificationManager $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Returns compiled notification text.
     *
     * @return array|string
     * @throws \yii\base\Exception
     */
    public function getCompiledText()
    {
        return $this->owner->compileText($this->getType(), $this->getData());
    }
}