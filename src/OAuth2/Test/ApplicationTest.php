<?php

namespace Eole\Sandstone\OAuth2\Test;

use Eole\Sandstone\Tests\AbstractApplicationTest;

class ApplicationTest extends AbstractApplicationTest
{
    public function testCreateAccessToken()
    {
        $client = $this->createClient();

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'password',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'username' => 'existing-player',
            'password' => 'good-password',
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $result = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('access_token', $result);
        $this->assertNotEmpty($result->access_token);
    }

    public function testCreateAccessTokenForbiddenIfPasswordWrong()
    {
        $client = $this->createClient();

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'password',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'username' => 'existing-player',
            'password' => 'wrong-password',
        ));

        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testRefreshToken()
    {
        $client = $this->createClient();

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'password',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'username' => 'existing-player',
            'password' => 'good-password',
        ));

        $accessToken = json_decode($client->getResponse()->getContent());

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'refresh_token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => $accessToken->refresh_token,
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $freshAccessToken = json_decode($client->getResponse()->getContent());

        $this->assertObjectHasAttribute('access_token', $freshAccessToken);
        $this->assertNotEmpty($freshAccessToken->access_token);
        $this->assertObjectHasAttribute('refresh_token', $freshAccessToken);
        $this->assertNotEmpty($freshAccessToken->refresh_token);
    }

    public function testRefreshTokenReturnsClientErrorOnInvalidRefreshToken()
    {
        $client = $this->createClient();

        $client->request('POST', '/oauth/access-token', array(
            'grant_type' => 'refresh_token',
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => 'invalidrefreshtoken',
        ));

        $this->assertTrue($client->getResponse()->isClientError());
    }
}
