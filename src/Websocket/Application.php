<?php

namespace Eole\Sandstone\Websocket;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Eole\Sandstone\Application as SilexApplication;

class Application implements WampServerInterface
{
    /**
     * @var SilexApplication
     */
    private $silexApp;

    /**
     * @var Topic[]
     */
    private $topics;

    /**
     * @param SilexApplication $silexApp
     */
    public function __construct(SilexApplication $silexApp)
    {
        $this->silexApp = $silexApp;
        $this->topics = array();
    }

    /**
     * @param ConnectionInterface $conn
     *
     * @return UserInterface|null
     *
     * @throws \Exception
     */
    private function authenticateUser(ConnectionInterface $conn)
    {
        $accessToken = $conn->WebSocket->request->getQuery()->get('access_token');

        if (null === $accessToken) {
            return null;
        }

        $authenticationManager = $this->silexApp['security.authentication_manager'];

        $authenticatedToken = $authenticationManager->authenticate(new OAuth2Token($accessToken));
        $user = $authenticatedToken->getUser();
        $isUser = $user instanceof UserInterface;

        if (!$isUser) {
            throw new \Exception('User not found.');
        }

        return $user;
    }

    /**
     * {@InheritDoc}
     */
    public function onOpen(ConnectionInterface $conn)
    {
        echo __METHOD__.' authentication... ';

        try {
            $user = $this->authenticateUser($conn);
            if (null === $user) {
                echo 'Anonymous connection.'.PHP_EOL;
            } else {
                echo sprintf('User "%s" logged.'.PHP_EOL, $user->getUsername());
            }
        } catch (\Exception $e) {
            echo 'failed: '.$e->getMessage().PHP_EOL;
            $conn->send(json_encode('Could not authenticate client, closing connection.'));
            $conn->close();

            return;
        }

        $conn->user = $user;
    }

    /**
     * {@InheritDoc}
     */
    private function getTopic($topicPath)
    {
        if (!isset($this->topics[$topicPath])) {
            $this->topics[$topicPath] = $this->loadTopic($topicPath);
        }

        return $this->topics[$topicPath];
    }

    /**
     * @param string $topicPath
     *
     * @return Topic
     */
    private function loadTopic($topicPath)
    {
        $topic = $this->silexApp['sandstone.websocket.router']->loadTopic($topicPath);

        $topic->setNormalizer($this->silexApp['serializer']);

        if ($topic instanceof EventSubscriberInterface) {
            $this->silexApp['dispatcher']->addSubscriber($topic);
        }

        return $topic;
    }

    /**
     * {@InheritDoc}
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->getTopic($topic)->onSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->topics[$topic]->onPublish($conn, $topic, $event);
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        echo __METHOD__.' '.$topic.PHP_EOL;

        $this->topics[$topic]->onUnSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        echo __METHOD__.PHP_EOL;

        foreach ($this->topics as $topic) {
            if ($topic->has($conn)) {
                $topic->onUnSubscribe($conn, $topic);
            }
        }
    }

    /**
     * {@InheritDoc}
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        echo __METHOD__.PHP_EOL;
    }

    /**
     * {@InheritDoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo __METHOD__.' '.$e->getMessage().PHP_EOL;
    }
}
