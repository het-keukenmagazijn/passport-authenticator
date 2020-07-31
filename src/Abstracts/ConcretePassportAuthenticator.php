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
    private $_oauth2Authenticator;

    /**
     * @param string $endpoint
     * @param array $dataToSend
     * @param array $extraHeaders
     * @param bool $debugOutput <See what the request is sending, without requiring authentication.>
     * @return array
     * @throws \Exception
     */
    final public function get(string $endpoint, array $dataToSend = [], array $extraHeaders = [], bool $debugOutput = false): array {
        if (!$debugOutput) {
            if (!$this->isAuthenticated()) $this->_authenticate();
        }
        return $this->sendGetRequest($endpoint, $dataToSend, $extraHeaders, $debugOutput);
    }

    /**
     * @param string $endpoint
     * @param array $dataToSend
     * @param array $extraHeaders
     * @param bool $debugOutput <See what the request is sending, without requiring authentication.>
     * @return array
     * @throws \Exception
     */
    final public function post(string $endpoint, array $dataToSend = [], array $extraHeaders = [], bool $debugOutput = false): array {
        if (!$debugOutput) {
            if (!$this->isAuthenticated()) $this->_authenticate();
        }
        return $this->sendPostRequest($endpoint, $dataToSend, $extraHeaders, $debugOutput);
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
            ->currentlyHasAToken() && !$this->getAuthenticator()->isTokenExpired();
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
        $_sendableData = array_merge($this->getHeaders($extraHeaders), $dataToSend);
        $_clientCall = $this->_getGuzzleClient()
            ->get($endpoint, array_merge($_sendableData, $extraHeaders));
        $_returnData = json_decode((string) $_clientCall->getBody());
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
        $_sendableData = array_merge($this->getHeaders($extraHeaders), $dataToSend);
        $_clientCall = $this->_getGuzzleClient()
            ->post($endpoint, array_merge($_sendableData, $extraHeaders));
        $_returnData = json_decode((string) $_clientCall->getBody());
        if (is_object($_returnData)) $_returnData = (array) $_returnData;
        return $_returnData;
    }

    /**
     * Calculates the custom headers to send with POST- and GET requests.
     * @param array|null $extraHeaders
     * @param bool $skipAccessToken
     * @return array
     */
    public function getHeaders(?array $extraHeaders, bool $skipAccessToken = false): array {
        $_accessToken = $skipAccessToken ? null : $this->_getAuthenticator()->getCredentials()->access_token;
        return [
            'headers' => array_merge([
                'Authorization' => sprintf("Bearer %s", $_accessToken),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ], $extraHeaders ?? [])
        ];
    }

    /**
     * @return Client
     */
    protected final function _getGuzzleClient() : Client {
	return new \GuzzleHttp\Client([
                'base_uri' => $this->getBaseUri()
            ]);

	// Disabled because Guzzle uses a singleton?
        /*if (empty(self::$_guzzleClient)) {
            self::$_guzzleClient = new \GuzzleHttp\Client([
                'base_uri' => $this->getBaseUri()
            ]);
        }
	return self::$_guzzleClient;*/
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
