<?php

namespace Eole\Sandstone\OAuth2\Storage;

use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Storage\AbstractStorage;

class Client extends AbstractStorage implements ClientInterface
{
    /**
     * @var array[]
     */
    private $clients;

    /**
     * @param array[] $clients
     */
    public function __construct(array $clients)
    {
        $this->clients = $clients;
    }

    /**
     * {@InheritDoc}
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        foreach ($this->clients as $client) {
            if (($client['id'] === $clientId) && ($client['secret'] === $clientSecret)) {
                $clientEntity = new ClientEntity($this->server);

                return $clientEntity->hydrate($client);
            }
        }

        return null;
    }

    /**
     * {@InheritDoc}
     */
    public function getBySession(SessionEntity $session)
    {
        throw new \Eole\Sandstone\OAuth2\Exception\NotImplementedException();
    }
}
