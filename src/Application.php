<?php

namespace Eole\Sandstone;

use Alcalyn\AuthorizationHeaderFix\AuthorizationHeaderFixListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Silex\Controller;
use Silex\Application as BaseApplication;
use Eole\Sandstone\Websocket\ServiceProvider as WebsocketServiceProvider;
use Eole\Sandstone\Push\ServiceProvider as PushServiceProvider;

class Application extends BaseApplication
{
    /**
     * {@InheritDoc}
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->fixAuthorizationHeader();
    }

    /**
     * Use AuthorizationHeaderFix to add Authorization header in Symfony requests.
     */
    private function fixAuthorizationHeader()
    {
        $this['sandstone.listener.authorization_header_fix'] = function () {
            return new AuthorizationHeaderFixListener();
        };

        $this->on(
            KernelEvents::REQUEST,
            array(
                $this['sandstone.listener.authorization_header_fix'],
                'onKernelRequest'
            ),
            10
        );
    }

    /**
     * Add a new topic route.
     *
     * @param string $pattern
     * @param callable $factory
     *
     * @return Controller
     */
    public function topic($pattern, callable $factory)
    {
        if (!$this->offsetExists('sandstone.websocket.topics')) {
            throw new \LogicException(sprintf(
                'You must register Websocket server service provider (%s) in order to use %s method.',
                WebsocketServiceProvider::class,
                __METHOD__
            ));
        }

        return $this['sandstone.websocket.topics']->match($pattern, $factory);
    }

    /**
     * Returns whether Push server is registered and enabled.
     *
     * @return bool
     */
    public function isPushEnabled()
    {
        return $this->offsetExists('sandstone.push') && $this['sandstone.push.enabled'];
    }

    /**
     * Automatically forward rest API event to push server.
     *
     * @param string $eventName
     *
     * @return self
     */
    public function forwardEventToPushServer($eventName)
    {
        return $this->forwardEventsToPushServer([$eventName]);
    }

    /**
     * Automatically forward rest API events to push server.
     *
     * @param string[] $eventsNames
     *
     * @return self
     */
    public function forwardEventsToPushServer(array $eventsNames)
    {
        if (!$this->offsetExists('sandstone.push')) {
            throw new \LogicException(sprintf(
                'You must register a Push server service provider (%s) in order to use %s method.',
                PushServiceProvider::class,
                __METHOD__
            ));
        }

        $this->before(function () use ($eventsNames) {
            $this['sandstone.push.event_forwarder']->forwardAllEvents($eventsNames);
        });

        return $this;
    }
}
