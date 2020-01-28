<?php

use Keukenmagazijn\PassportAuthenticator\Instances\Authenticator;
use Keukenmagazijn\PassportAuthenticator\Mockables\Mock_ConcretePassportAuthenticator;
use Keukenmagazijn\PassportAuthenticator\Mockables\Mock_ConcretePassportAuthenticatorFactory;

class ConcretePassportAuthenticator_FeatureTest extends \Tests\TestCase
{
    /** @var Mock_ConcretePassportAuthenticator */
    private static $_sut;

    protected function setUp(): void
    {
        parent::setUp();
        self::$_sut = \App::make(Mock_ConcretePassportAuthenticator::class);
    }

    public function test_if_request_headers_get_generated_properly()
    {
        $_headers = self::$_sut->getHeaders(['Test-Data' => '1,2,3,4'], true);
        $this->assertIsArray($_headers);
        $this->assertArrayHasKey('headers', $_headers);
        $this->assertArrayHasKey('Test-Data', $_headers['headers']);
        $this->assertEquals('1,2,3,4', $_headers['headers']['Test-Data']);
    }
}
