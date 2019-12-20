<?php namespace Keukenmagazijn\PassportAuthenticator\Factories;

use Keukenmagazijn\PassportAuthenticator\Contracts\IsFactory;
use Keukenmagazijn\PassportAuthenticator\Instances\Authenticator;

abstract class ConcretePassportAuthenticatorFactory implements IsFactory
{
    /**
     * @param string $appName
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $userEmail
     * @param string $userPassword
     * @param string $authorizeUri
     * @param string $tokenUri
     * @return Authenticator
     */
    public function buildAuthenticator(
        string $appName,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $userEmail,
        string $userPassword,
        string $authorizeUri,
        string $tokenUri
    ): Authenticator {
        /** @var Authenticator $_authenticator */
        $_authenticator = \App::make(Authenticator::class);
        $_authenticator->setAppName($appName);
        $_authenticator->setClientId($clientId);
        $_authenticator->setSecret($clientSecret);
        $_authenticator->setRedirectUri($redirectUri);
        $_authenticator->setUserEmail($userEmail);
        $_authenticator->setUserPassword($userPassword);
        $_authenticator->setAuthorizeUri($authorizeUri);
        $_authenticator->setTokenUri($tokenUri);
        return $_authenticator;
    }
}
