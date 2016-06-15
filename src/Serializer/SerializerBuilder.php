<?php

namespace Eole\Sandstone\Serializer;

use Alcalyn\SerializerDoctrineProxies\DoctrineProxyHandler;
use Alcalyn\SerializerDoctrineProxies\DoctrineProxySubscriber;
use JMS\Serializer\EventDispatcher\EventDispatcher;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder as BaseSerializerBuilder;

class SerializerBuilder extends BaseSerializerBuilder
{
    /**
     * {@InheritDoc}
     *
     * Configure builder for Eole Api,
     * and use alcalyn/serializer-doctrine-proxies stuff,
     * which disable lazy loading during serialization.
     *
     * @return BaseSerializerBuilder
     */
    public static function create()
    {
        return parent::create()
            ->setDefaultSerializationContextFactory(array(self::class, 'createDefaultSerializationContext'))
            ->addDefaultHandlers()
            ->configureListeners(function (EventDispatcher $dispatcher) {
                $proxySubscriber = new DoctrineProxySubscriber(false);
                $dispatcher->addSubscriber($proxySubscriber);
            })
            ->configureHandlers(function (HandlerRegistryInterface $handlerRegistry) {
                $proxyHandler = new DoctrineProxyHandler();
                $handlerRegistry->registerSubscribingHandler($proxyHandler);
            })
        ;
    }

    /**
     * Create a default serialization context for Eole Api.
     *
     * @return SerializationContext
     */
    public static function createDefaultSerializationContext()
    {
        return SerializationContext::create()
            ->setSerializeNull(true)
            ->setGroups(array('Default'))
            ->enableMaxDepthChecks()
        ;
    }
}
