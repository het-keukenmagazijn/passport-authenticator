<?php namespace Keukenmagazijn\PassportAuthenticator\Contracts;

use Keukenmagazijn\PassportAuthenticator\Entities\ExternalOauth2Credential;

interface AuthenticatorInterface
{
    public function authorize(): ExternalOauth2Credential;
    public function refreshOauth2Token(): void;
    public function currentlyHasAToken(): bool;
    public function getCredentials():? ExternalOauth2Credential;
    public function setAppName(string $appName): void;
    public function getAppName(): string;
    public function getSecret(): string;
    public function setSecret(string $secret): void;
    public function getClientId(): string;
    public function setClientId(string $clientId): void;
    public function getRedirectUri(): string;
    public function setRedirectUri(string $redirectUri): void;
    public function getAuthorizeUri(): string;
    public function setAuthorizeUri(string $authorizeUri): void;
    public function getTokenUri(): string;
    public function setTokenUri(string $tokenUri): void;
}
