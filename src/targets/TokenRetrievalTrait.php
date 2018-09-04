<?php
/**
 * by Aleksandar Panic
 * Company: 2amigOS!
 *
 **/

namespace dvamigos\Yii2\Notifications\targets;


use dvamigos\Yii2\Notifications\NotificationInterface;
use dvamigos\Yii2\Notifications\TokenRetrievalInterface;
use yii\di\Instance;

trait TokenRetrievalTrait
{
    /**
     * Token retrieval handler.
     *
     * If it's callable then it's called using format:
     * function(NotificationInterface $n) {
     *     return 'token';
     * }
     *
     * If it's a class which implements TokenRetrievalInterface then it's called:
     * $tokenRetriever->getToken($targetName, $notification) // Should return token.
     *
     * @var array|string|callable
     */
    public $tokenRetriever;

    /**
     * @var TokenRetrievalInterface|null
     */
    protected $tokenRetrieverInstance = null;

    public function getToken(NotificationInterface $n)
    {
        if (is_callable($this->tokenRetriever)) {
            return call_user_func_array($this->tokenRetriever, [$n]);
        }

        if ($this->tokenRetrieverInstance === null) {
            $this->tokenRetrieverInstance = Instance::ensure($this->tokenRetriever, TokenRetrievalInterface::class);
        }

        return $this->tokenRetrieverInstance->getToken($n);
    }
}