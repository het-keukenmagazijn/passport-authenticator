<?php namespace Keukenmagazijn\PassportAuthenticator\Instances;

use Keukenmagazijn\PassportAuthenticator\Contracts\AuthenticatorInterface;
use Keukenmagazijn\PassportAuthenticator\Entities\ExternalOauth2Credential;
use Keukenmagazijn\PassportAuthenticator\Repositories\Contracts\ExternalOauth2CredentialsRepositoryInterface;
use Keukenmagazijn\PassportAuthenticator\Repositories\Eloquent\EloquentExternalOauth2CredentialsRepository;
use GuzzleHttp\Client;

class Authenticator implements AuthenticatorInterface
{
    /** @var string */
    private $_appName;
    /** @var string */
    private $_secret;
    /** @var string */
    private $_clientId;
    /** @var string */
    private $_redirectUri;
    /** @var string */
    private $_authorizeUri;
    /** @var string */
    private $_userPassword;
    /** @var string */
    private $_userEmail;
    /** @var string */
    private $_tokenUri;
    /** @var Client */
    private static $_guzzleClient;
    /** @var ExternalOauth2CredentialsRepositoryInterface */
    private static $_repository;
    /** @var ExternalOauth2Credential */
    private static $_credentials;

    /**
     * USE password client credentials! <https://laravel.com/docs/5.7/passport#password-grant-tokens>
     * @return ExternalOauth2Credential
     * @throws \Exception
     */
    public function authorize(): ExternalOauth2Credential {
        if (!$this->currentlyHasAToken()) {
            $_credentials = $this->_getAuthenticationCredentialsFromExternalApplication();
            $this->_writeCredentialsToDatabase($_credentials);
        }
        $_credentials = $this->getCredentials();
        if ($_credentials->expires_at <= date('Y-m-d H:i:s', strtotime(now()))) {
            $this->refreshOauth2Token();
        }
        return $_credentials;
    }

    /**
     * @return void
     */
    public function refreshOauth2Token(): void {
        $_response = $this->_getGuzzleClient()->post($this->getTokenUri(), [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->getCredentials()->refresh_token,
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getSecret(),
                'scope' => '',
            ],
        ]);
        $_newCredentials = (array) json_decode((string) $_response->getBody());
        $this->getCredentials()->update([
            'access_token' => $_newCredentials['access_token'],
            'refresh_token' => $_newCredentials['refresh_token'],
            'expires_at' => date('Y-m-d H:i:s', strtotime(now())+$_newCredentials['expires_in'])
        ]);
    }

    /**
     * @return bool
     */
    public function currentlyHasAToken(): bool {
        return ($this->getAppName() && !empty($this->getCredentials()));
    }

    /**
     * @return array|null
     */
    public function getCredentials():? ExternalOauth2Credential {
        if (empty(self::$_credentials)) {
            $_credentials = $this->_getRepository()->findByKey('app_name', $this->getAppName());
            if (0 < $_credentials->count()) {
                self::$_credentials = $_credentials->first();
            } else {
                self::$_credentials = null;
            }

        }
        return self::$_credentials;
    }

    /**
     * @param array $credentials
     * @return void
     * @throws \Exception
     */
    private function _writeCredentialsToDatabase(array $credentials): void {
        /** @var EloquentExternalOauth2CredentialsRepository $_repository */
        $resource = $this->_getRepository()->createResourceByArray([
            'app_name' => $this->getAppName(),
            'access_token' => $credentials['access_token'],
            'refresh_token' => $credentials['refresh_token'],
            'expires_at' => date('Y-m-d H:i:s', strtotime(now())+$credentials['expires_in'])
        ]);
        if (empty(self::$_credentials)) self::$_credentials = $resource;
    }

    /**
     * @return array
     */
    private function _getAuthenticationCredentialsFromExternalApplication(): array {
        $response = $this->_getGuzzleClient()->post($this->getTokenUri(), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $this->getClientId(),
                'client_secret' => $this->getSecret(),
                'username' => $this->getUserEmail(),
                'password' => $this->getUserPassword(),
                'scope' => '',
            ],
        ]);
        return (array) json_decode((string) $response->getBody(), true);
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->_secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->_secret = $secret;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->_clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void
    {
        $this->_clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->_redirectUri;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri(string $redirectUri): void
    {
        $this->_redirectUri = $redirectUri;
    }

    /**
     * @param string $appName
     */
    public function setAppName(string $appName): void
    {
        $this->_appName = $appName;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->_appName;
    }

    /**
     * @return string
     */
    public function getAuthorizeUri(): string
    {
        return $this->_authorizeUri;
    }

    /**
     * @param string $authorizeUri
     */
    public function setAuthorizeUri(string $authorizeUri): void
    {
        $this->_authorizeUri = $authorizeUri;
    }

    /**
     * @return Client
     */
    private function _getGuzzleClient() : Client {
        if (empty(self::$_guzzleClient)) {
            self::$_guzzleClient = new \GuzzleHttp\Client();
        }
        return self::$_guzzleClient;
    }

    /**
     * @return string
     */
    public function getUserPassword(): string
    {
        return $this->_userPassword;
    }

    /**
     * @param string $userPassword
     */
    public function setUserPassword(string $userPassword): void
    {
        $this->_userPassword = $userPassword;
    }

    /**
     * @return string
     */
    public function getUserEmail(): string
    {
        return $this->_userEmail;
    }

    /**
     * @param string $userEmail
     */
    public function setUserEmail(string $userEmail): void
    {
        $this->_userEmail = $userEmail;
    }

    /**
     * @return string
     */
    public function getTokenUri(): string
    {
        return $this->_tokenUri;
    }

    /**
     * @param string $tokenUri
     */
    public function setTokenUri(string $tokenUri): void
    {
        $this->_tokenUri = $tokenUri;
    }

    /**
     * @return ExternalOauth2CredentialsRepositoryInterface|mixed
     */
    private function _getRepository() {
        if (empty(self::$_repository)) {
            self::$_repository = \App::make(ExternalOauth2CredentialsRepositoryInterface::class);
        }
        return self::$_repository;
    }
}
