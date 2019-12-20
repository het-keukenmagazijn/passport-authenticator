<?php namespace Keukenmagazijn\PassportAuthenticator\Repositories\Eloquent;

use Keukenmagazijn\PassportAuthenticator\Entities\ExternalOauth2Credential;
use Keukenmagazijn\PassportAuthenticator\Repositories\Contracts\ExternalOauth2CredentialsRepositoryInterface;
use \RKooistra\SuperEloquentRepository\Abstracts\ConcreteResourceRepository;

class EloquentExternalOauth2CredentialsRepository extends ConcreteResourceRepository implements ExternalOauth2CredentialsRepositoryInterface
{
    /** @return string <Return the full path to the class here.> */
    protected function getModelClass(): string
    {
        return ExternalOauth2Credential::class;
    }
}
