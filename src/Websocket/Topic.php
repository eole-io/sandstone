<?php

namespace Eole\Sandstone\Websocket;

use JMS\Serializer\NormalizerInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\Topic as BaseTopic;

class Topic extends BaseTopic
{
    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var NormalizerInterface
     */
    protected $normalizer;

    /**
     * @param string $topicPath
     * @param array $arguments
     */
    public function __construct($topicPath, array $arguments = array())
    {
        parent::__construct($topicPath);

        $this->arguments = $arguments;
    }

    /**
     * @param NormalizerInterface $normalizer
     *
     * @return self
     */
    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;

        return $this;
    }

    /**
     * {@InheritDoc}
     *
     * And normalize message using the serializer before send.
     */
    public function broadcast($msg, array $exclude = array(), array $eligible = array())
    {
        parent::broadcast($this->normalizer->toArray($msg), $exclude, $eligible);
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     */
    public function onSubscribe(WampConnection $conn, $topic)
    {
        $this->add($conn);
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     * @param string $event
     */
    public function onPublish(WampConnection $conn, $topic, $event)
    {
        // noop
    }

    /**
     * @param WampConnection $conn
     * @param string $topic
     */
    public function onUnSubscribe(WampConnection $conn, $topic)
    {
        $this->remove($conn);
    }
}
