<?php

namespace Eole\Sandstone\Websocket\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Ratchet\ConnectionInterface;

class WebsocketAuthenticationEvent extends ConnectionEvent
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @param ConnectionInterface $conn
     * @param UserInterface $user
     */
    public function __construct(ConnectionInterface $conn, UserInterface $user)
    {
        parent::__construct($conn);

        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
