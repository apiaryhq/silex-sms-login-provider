#  Silex SMS Login Provider

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Silex add on to provide SMS based login service. This is a 2-step process. First the user 

## Install

Via Composer

``` bash
$ composer require apiaryhq/silex-sms-login-provider
```

## Usage

To use SMS login, register an SMS handler. This is a service that supports the SmsHandlerInterface. An inplementation is provided that uses 
[Twilio](https://www.twilio.com/) to send SMS messages.

``` php
$accountSid = getenv('TWILIO_ACCOUNT_SID');
$authToken = getenv('TWILIO_AUTH_TOKEN');
$app->register(new TwilioSMSHandlerProvider(), [
    'sms.handler.from' => 'Daz',
    'sms.handler.twilio_sid' => $accountSid,
    'sms.handler.twilio_auth_token' => $authToken,
]);
```

Use the provider to register services and controller:

``` php
$smsLoginProvider = new SmsLoginProvider();
$app->register($smsLoginProvider);
$app->mount('', $smsLoginProvider);
```

Protect an area of the site with SMS login:

``` php
$app['security.firewalls'] = array(
    // Login page is accessible to all:
    'login' => array(
        'pattern' => '^/login$',
    ),
    // Everything else is secured:
    'secured_area' => array(
        'pattern' => '^.*$',
        'sms' => true,
        'logout' => ['logout_path' => '/logout'],
        'users' => $app->share(function($app) { return $app['user.manager']; }),
    ),
);
```

Note that the `$app['user.manager']` must be an implementation of `UserProviderInterface`
and also provide mobile phone numbers as usernames. 

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email darren@apiaryhq.com instead of using the issue tracker.

## Credits

- [Darren Mothersele][http://www.darrenmothersele.com/]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/league/:package_name.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/thephpleague/:package_name/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/thephpleague/:package_name.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/thephpleague/:package_name.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/league/:package_name.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/league/:package_name
[link-travis]: https://travis-ci.org/thephpleague/:package_name
[link-scrutinizer]: https://scrutinizer-ci.com/g/thephpleague/:package_name/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/thephpleague/:package_name
[link-downloads]: https://packagist.org/packages/league/:package_name
[link-author]: http://www.darrenmothersele.com/
[link-contributors]: ../../contributors
