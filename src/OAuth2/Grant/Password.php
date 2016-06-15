<?php

namespace Eole\Sandstone\OAuth2\Grant;

use League\OAuth2\Server\Grant\PasswordGrant;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class Password extends PasswordGrant
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @param UserProviderInterface $userProvider
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(UserProviderInterface $userProvider, EncoderFactoryInterface $encoderFactory)
    {
        $this->userProvider = $userProvider;
        $this->encoderFactory = $encoderFactory;

        $this->init();
    }

    /**
     * Init.
     */
    private function init()
    {
        $this->setVerifyCredentialsCallback(function ($username, $password) {
            $user = $this->userProvider->loadUserByUsername($username);

            if (null === $user) {
                return false;
            }

            $encoder = $this->encoderFactory->getEncoder($user);
            $isPasswordValid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());

            if ($isPasswordValid) {
                return $username;
            } else {
                return false;
            }
        });
    }
}
