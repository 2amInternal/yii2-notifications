<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

use dvamigos\Yii2\Notifications\NotificationInterface;
use yii\helpers\Json;

/**
 * Class NotificationModel
 *
 * @property string $id
 * @property string $type
 * @property string $data
 * @property string $created_at
 * @property string $updated_at
 * @property string $created_by
 * @property string $updated_by
 */
class NotificationModel extends \yii\db\ActiveRecord implements NotificationInterface
{
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * Returns notification by ID.
     *
     * @param $id int ID of the notification
     * @return NotificationInterface|null
     */
    public static function findById($id)
    {
        return static::findOne((int)$id);
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
}