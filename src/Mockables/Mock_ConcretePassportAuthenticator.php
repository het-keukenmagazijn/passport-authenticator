<?php namespace Keukenmagazijn\PassportAuthenticator\Mockables;

use Keukenmagazijn\PassportAuthenticator\Abstracts\ConcretePassportAuthenticator;
use Keukenmagazijn\PassportAuthenticator\Factories\ConcretePassportAuthenticatorFactory;
use Keukenmagazijn\PassportAuthenticator\Instances\Authenticator;

class Mock_ConcretePassportAuthenticator extends ConcretePassportAuthenticator
{
    /**
     * @inheritDoc
     */
    protected function getAuthenticatorInstance(): Authenticator
    {
        return app()->make(Mock_ConcretePassportAuthenticatorFactory::class)->build();
    }

    /**
     * @inheritDoc
     */
    protected function getBaseUri(): string
    {
        return 'https://google.com';
    }
}
