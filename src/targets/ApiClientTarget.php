<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;

use dvamigos\Yii2\Notifications\NotificationManager;
use dvamigos\Yii2\Notifications\NotificationTargetInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

abstract class ApiClientTarget extends BaseObject implements NotificationTargetInterface
{
    /** @var NotificationManager */
    protected $owner;

    /**
     * @var Client
     */
    protected $client = null;

    /**
     * Sets storage owner component.
     *
     * @param NotificationManager $owner
     */
    public function setOwner(NotificationManager $owner)
    {
        $this->owner = $owner;
    }

    public function init()
    {
        if (!class_exists('GuzzleHttp\Client')) {
            throw new Exception('Guzzle HTTP client is required for this target.');
        }

        parent::init();
    }

    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new Client(ArrayHelper::merge([
                'base_uri' => $this->getBaseApiUrl()
            ], $this->clientOptions()));
        }

        return $this->client;
    }

    protected function sendRequest($method, $resource, $request = [])
    {
        try {
            return $this->getClient()->request($method, $resource, ArrayHelper::merge($this->requestOptions(), $request));
        } catch (ClientException $e) {
            return $this->handleClientException($e);
        } catch (ServerException $e) {
            return $this->handleServerException($e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function handleClientException(ClientException $e)
    {
        throw $e;
    }

    protected function handleServerException(ServerException $e)
    {
        throw $e;
    }

    public abstract function getBaseApiUrl();

    protected function clientOptions()
    {
        return [];
    }

    public function requestOptions()
    {
        return [];
    }
}