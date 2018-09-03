<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications;


use yii\base\BaseObject;

class Notification extends BaseObject implements NotificationInterface
{
    /** @var NotificationManager */
    public $owner;

    /**
     * @var int Notification ID
     */
    public $id;

    /**
     * @var string Notification type
     */
    public $type;

    /**
     * @var array Notification data
     */
    public $data;

    /**
     * @var int Notification timestamp
     */
    public $timestamp;

    /**
     * @var bool Whether or not notification is read.
     */
    public $isRead;


    /**
     * Returns user ID owner of this notification
     *
     * @var int
     */
    public $userId;

    /**
     * Sets notification owner component
     *
     * @param NotificationManager $owner
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
     * Sets current notification ID.
     *
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
        return $this->data;
    }

    /**
     * Returns timestamp of this notification.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Returns whether or not notification is read.
     *
     * @return bool
     */
    public function isRead()
    {
        return $this->isRead;
    }

    /**
     * Returns user which owns this notification
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets type of this notification.
     * @param $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Sets data for this notification. Which will be used when displaying notification info.
     *
     * @param $data array Data which will be set
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Sets timestamp of this notification
     *
     * @param $timestamp int UNIX timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Sets if this notification is read.
     *
     * @param $isRead bool
     */
    public function setRead($isRead)
    {
        $this->isRead = $isRead;
    }

    /**
     * Sets user id.
     *
     * @param $userId int User ID
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}