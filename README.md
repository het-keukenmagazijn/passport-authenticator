# Laravel Passport Authenticator
## Introduction
This package is a self-refreshing [Laravel Passport](https://laravel.com/docs/5.8/passport) authenticator. There are superclasses available with which you can make quick custom connectors for all your (micro)services and you will never have to worry about having to maintain, refresh or create your bearer tokens yourself.

## How to install
- You can install the package by requiring the `keukenmagazijn/passport-authenticator` package, or run `composer require keukenmagazijn/passport-authenticator` in your project root directory.
- When your package is included, you should run `php artisan migrate` to create the `external_oauth2_credentials` table, which the package requires to maintain your tokens.

## How to use the package.
### The setup
#### The configuration
The first recommended step is to create a config file for your passport, you don't *have* to but it's the clean thing to do. So let's say we have this config file available:
`config/external_application.php`
```php
return [
    'passport' => [
        'endpoints' => [
            'base_uri' => env('APP_BASE_URI', null), // https://external.app
            'authorize' => '/oauth/authorize', // Authorize our client and user route to retrieve a code.
            'token' => '/oauth/token' // Route we give our code to and get our tokens from.
        ],
        'user' => [
            'email' => env('APP_USER_EMAIL', null), // username of external app.
            'password' => env('APP_USER_PASSWORD', null), // password of external app.
        ],
        'secret' => env('APP_SECRET', null), // API client secret used to connect.
        'client_id' => env('APP_CLIENT_ID', null), // API client id used to connect .
        'redirect_uri' => env('APP_REDIRECT_URI', null), // API redirect uri.
    ]
];
```

#### Extending the Library superclasses
This library offers two superclasses, these need to be extended and available in order for the library to do its work properly.

##### The Authenticator
The `Keukenmagazijn\PassportAuthenticator\Abstracts\ConcretePassportAuthenticator` code handles all the magic for your credentials. Let's make our own custom authenticator.

`app/Components/PassportAuthenticators/Instances/ExampleAuthenticator.php`:
```php
<?php namespace App\Components\PassportAuthenticators\Instances;

use App\Components\PassportAuthenticators\Factories\ExampleFactory;
use Keukenmagazijn\PassportAuthenticator\Abstracts\ConcretePassportAuthenticator;
use Keukenmagazijn\PassportAuthenticator\Instances\Authenticator;

class ExampleAuthenticator extends ConcretePassportAuthenticator
{
    /**
     * We refer to the our factory here.
     * @return Authenticator
     */
    protected function getAuthenticatorInstance(): Authenticator
    {
        /** @var ExampleFactory $_factory */
        $_factory = \App::make(ExampleFactory::class);
        return $_factory->build();
    }

    /**
     * @return string
     */
    protected function getBaseUri(): string
    {
        return config('external_application.passport.endpoints.base_uri');
    }
}
```

Now we need to create a factory as the ConcretePassportAuthenticator depends on this to create a configured authenticator.

##### The Factory
- `Keukenmagazijn\PassportAuthenticator\Factories\ConcretePassportAuthenticatorFactory\`
This is the "superfactory" that we need to make an extension to. It contains a method which configures our Authenticator - which we will make after the factory - easily for us:

This is what the method looks like:
```php
public function buildAuthenticator(
    string $appName,
    string $clientId,
    string $clientSecret,
    string $redirectUri,
    string $userEmail,
    string $userPassword,
    string $authorizeUri,
    string $tokenUri
): Authenticator;
```

So, knowing the method we need to use, let's create our first custom authenticator:

`Components\PassportAuthenticators\Factories\ExampleFactory.php`:
```php
<?php namespace App\Components\PassportAuthenticators\Factories;

use Keukenmagazijn\PassportAuthenticator\Factories\ConcretePassportAuthenticatorFactory;
use Keukenmagazijn\PassportAuthenticator\Instances\Authenticator;

class ExampleFactory extends ConcretePassportAuthenticatorFactory
{
    /**
     * @return Authenticator
     */
    public function build(): Authenticator {
        return $this->buildAuthenticator(
            'external_example_app',
            config('external_application.passport.client_id'),
            config('external_application.passport.secret'),
            config('external_application.passport.redirect_uri'),
            config('external_application.passport.user.email'),
            config('external_application.passport.user.password'),
            sprintf("%s%s", config('external_application.passport.endpoints.base_uri'), config('external_application.passport.endpoints.authorize')),
            sprintf("%s%s", config('external_application.passport.endpoints.base_uri'), config('external_application.passport.endpoints.token'))
        );
    }
}
```

### A production use-case example
So now the set-up is done, you might wonder how you can actually use the package, so I'll show you an example of it!


