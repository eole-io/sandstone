<?php

namespace Eole\Sandstone\Websocket;

use Psr\Log\LoggerAwareTrait;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface as WrongEventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Eole\Sandstone\Logger\EchoLogger;
use Eole\Sandstone\OAuth2\Security\Authentication\Token\OAuth2Token;
use Eole\Sandstone\Websocket\Event\ConnectionEvent;
use Eole\Sandstone\Websocket\Event\WebsocketAuthenticationEvent;
use Eole\Sandstone\Websocket\Event\ConnectionErrorEvent;
use Eole\Sandstone\Websocket\Event\WampEvent;
use Eole\Sandstone\Websocket\Event\PublishEvent;
use Eole\Sandstone\Websocket\Event\RPCEvent;
use Eole\Sandstone\Application as SandstoneApplication;

final class Application implements WampServerInterface
{
    use LoggerAwareTrait;

    /**
     * @var SandstoneApplication
     */
    private $sandstoneApplication;

    /**
     * @var Topic[]
     */
    private $topics;

    /**
     * @param SandstoneApplication $sandstoneApplication
     */
    public function __construct(SandstoneApplication $sandstoneApplication)
    {
        $this->sandstoneApplication = $sandstoneApplication;
        $this->topics = array();
        $this->logger = new EchoLogger();
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

        $authenticationManager = $this->sandstoneApplication['security.authentication_manager'];

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
        $this->dispatch(ConnectionEvent::ON_OPEN, new ConnectionEvent($conn));

        $this->logger->info('Connection event', ['event' => 'open']);
        $this->logger->info('Authentication...');

        try {
            $user = $this->authenticateUser($conn);
            if (null === $user) {
                $this->logger->info('Anonymous connection');
            } else {
                $this->dispatch(ConnectionEvent::ON_AUTHENTICATION, new WebsocketAuthenticationEvent($conn, $user));
                $this->logger->info('User logged.', ['username' => $user->getUsername()]);
            }
        } catch (\Exception $e) {
            $this->logger->notice('Failed authentication', ['error_message' => $e->getMessage()]);
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
        $topic = $this->sandstoneApplication['sandstone.websocket.router']->loadTopic($topicPath);

        if (!$this->sandstoneApplication->offsetExists('serializer')) {
            throw new \RuntimeException('A serializer must be registered');
        }

        $topic->setNormalizer($this->sandstoneApplication['serializer']);

        if ($topic instanceof EventSubscriberInterface) {
            $this->sandstoneApplication['dispatcher']->addSubscriber($topic);
        }

        // debug purpose only. Sometimes I use the wrong namespace (JMS one), and it's hard to debug.
        if ($topic instanceof WrongEventSubscriberInterface) {
            throw new \LogicException(
                get_class($topic).' seems to implements the wrong EventSubscriberInterface. '.
                'Use the Symfony one, not the JMS one.'
            );
        }

        return $topic;
    }

    /**
     * {@InheritDoc}
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->dispatch(ConnectionEvent::ON_SUBSCRIBE, new WampEvent($conn, $topic));

        $this->logger->info('Topic event', ['event' => 'subscribe', 'topic' => $topic]);

        $this->getTopic($topic)->onSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $this->dispatch(ConnectionEvent::ON_PUBLISH, new PublishEvent($conn, $topic, $event, $exclude, $eligible));

        $this->logger->info('Topic event', ['event' => 'publish', 'topic' => $topic]);

        $this->getTopic($topic)->onPublish($conn, $topic, $event);
    }

    /**
     * {@InheritDoc}
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->dispatch(ConnectionEvent::ON_UNSUBSCRIBE, new WampEvent($conn, $topic));

        $this->logger->info('Topic event', ['event' => 'unsubscribe', 'topic' => $topic]);

        $this->getTopic($topic)->onUnSubscribe($conn, $topic);
    }

    /**
     * {@InheritDoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->dispatch(ConnectionEvent::ON_CLOSE, new ConnectionEvent($conn));

        $this->logger->info('Connection event', ['event' => 'close']);

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
        $this->dispatch(ConnectionEvent::ON_RPC, new RPCEvent($conn, $topic, $id, $params));

        $this->logger->info('Topic event', ['event' => 'call', 'topic' => $topic]);
    }

    /**
     * {@InheritDoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $this->dispatch(ConnectionEvent::ON_ERROR, new ConnectionErrorEvent($conn, $e));

        $this->logger->info('Connection event', ['event' => 'error', 'message' => $e->getMessage()]);
    }

    /**
     * Dispatch a ConnectionEvent to SandstoneApplication dispatcher.
     *
     * @param string $eventName
     * @param ConnectionEvent $event
     */
    private function dispatch($eventName, ConnectionEvent $event)
    {
        $this->sandstoneApplication['dispatcher']->dispatch($eventName, $event);
    }
}
