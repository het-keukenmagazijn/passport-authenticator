<?php namespace Keukenmagazijn\PassportAuthenticator\Abstracts;

use Keukenmagazijn\PassportAuthenticator\Contracts\SendsRequests;
use Keukenmagazijn\PassportAuthenticator\Instances\Authenticator;
use Keukenmagazijn\PassportAuthenticator\Entities\ExternalOauth2Credential;
use GuzzleHttp\Client;

abstract class ConcretePassportAuthenticator implements SendsRequests
{
    /** @var Client */
    private static $_guzzleClient;
    /**
     * @var Authenticator
     */
    private static $_oauth2Authenticator;

    /**
     * @param string $endpoint
     * @param array $dataToSend
     * @param array $extraHeaders
     * @return array
     * @throws \Exception
     */
    final public function get(string $endpoint, array $dataToSend = [], array $extraHeaders = []): array {
        if (!$this->isAuthenticated()) $this->_authenticate();
        return $this->sendGetRequest($endpoint, $dataToSend, $extraHeaders);
    }

    /**
     * @param string $endpoint
     * @param array $dataToSend
     * @param array $extraHeaders
     * @return array
     * @throws \Exception
     */
    final public function post(string $endpoint, array $dataToSend = [], array $extraHeaders = []): array {
        if (!$this->isAuthenticated()) $this->_authenticate();
        return $this->sendPostRequest($endpoint, $dataToSend, $extraHeaders);
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function _authenticate(): void {
        $this->_getAuthenticator()->authorize();
    }

    /**
     * @return bool
     */
    public function isAuthenticated (): bool {
        return $this->getAuthenticator()
            ->currentlyHasAToken();
    }

    /**
     * @return Authenticator
     */
    protected function getAuthenticator(): Authenticator {
        if (empty(self::$_oauth2Authenticator)) {
            self::$_oauth2Authenticator = $this->getAuthenticatorInstance();
        }
        return self::$_oauth2Authenticator;
    }

    /**
     * Return the configured Oauth2Authenticator for the application.
     * Use the Oauth2Authenticator Factory to do this.
     * @return Authenticator
     */
    abstract protected function getAuthenticatorInstance(): Authenticator;

    /**
     * Return the base URI of the website you want to call.
     * Format: http?s://www.application.com/.
     * @return string
     */
    abstract protected function getBaseUri(): string;

    /**
     * @param string $endpoint
     * @param array $dataToSend
     * @param array $extraHeaders
     * @return array
     */
    protected function sendGetRequest(string $endpoint, array $dataToSend = [], array $extraHeaders = []): array {
        $_sendableData = array_merge($this->getHeaders(), $dataToSend);
        $_returnData = json_decode(
            (string) $this->_getGuzzleClient()
                ->get($endpoint, array_merge($_sendableData, $extraHeaders))
                ->getBody()
        );
        if (is_object($_returnData)) $_returnData = (array) $_returnData;
        return $_returnData;
    }

    /**
     * @param string $endpoint
     * @param array $dataToSend
     * @param array $extraHeaders
     * @return array
     */
    protected function sendPostRequest(string $endpoint, array $dataToSend = [], array $extraHeaders = []): array {
        $_sendableData = array_merge($this->getHeaders(), $dataToSend);
        return json_decode(
            (string) $this->_getGuzzleClient()
                ->post($endpoint, array_merge($_sendableData, $extraHeaders))
                ->getBody()
        );
    }

    /**
     * @return array
     */
    protected function getHeaders(): array {
        return [
            'headers' => [
                'Authorization' => "Bearer " . $this->_getAuthenticator()->getCredentials()->access_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ]
        ];
    }

    /**
     * @return Client
     */
    protected final function _getGuzzleClient() : Client {
        if (empty(self::$_guzzleClient)) {
            self::$_guzzleClient = new \GuzzleHttp\Client([
                'base_uri' => $this->getBaseUri()
            ]);
        }
        return self::$_guzzleClient;
    }

    /**
     * @return Authenticator
     */
    private function _getAuthenticator(): Authenticator {
        if (empty(self::$_oauth2Authenticator)) {
            self::$_oauth2Authenticator = $this->getAuthenticator();
        }
        return self::$_oauth2Authenticator;
    }
}
