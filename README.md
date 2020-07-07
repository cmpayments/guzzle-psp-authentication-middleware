Guzzle Authentication Middleware
=============

[![Build Status][badge-build]][build]
[![Scrutinizer][badge-quality]][quality]
[![Software License][badge-license]][license]
[![Total Downloads][badge-downloads]][downloads]
[![Code Coverage][badge-coverage]][coverage]

This middleware implements the authentication part of a guzzle request to the CM Payments PSP-api.

This library is compatible with PHP 5.5+ and PHP 7.0, but requires the Guzzle, PHP HTTP client version ^6.0 or ^7.0

## Installation

To install cmpayments/guzzle-psp-authentication-middleware just require it with composer:
```
# composer require cmpayments/guzzle-psp-authentication-middleware
```


## Usage examples

Below an example in order to request an iDEAL-transaction via a POST-request.

```php
<?php
    $key = 'Your-OAuth-Consumer-Key';
    $secret = 'Your-OAuth-Consumer-Secret';

    $body = [
        'amount'   => 15.95,
        'currency' => 'EUR',
        'payments' => [
            [
                'amount'          => 15.95,
                'currency'        => 'EUR',
                'payment_method'  => 'iDEAL',
                'payment_details' => [
                    'issuer_id'     => 'RABONL2U',
                    'purchase_id'   => 'unqiue' . (new \DateTime())->format('YmdHis'),
                    'description'   => 'Transaction description',
                    'success_url'   => 'http://www.yourdomain.com/ideal/success',
                    'failed_url'    => 'http://www.yourdomain.com/ideal/failed',
                    'cancelled_url' => 'http://www.yourdomain.com/ideal/cancelled',
                    'expired_url'   => 'http://www.yourdomain.com/ideal/expired'
                ]
            ],

        ],
    ];

    $stack = HandlerStack::create();
    $authenticationMiddleware = new AuthenticationMiddleware($key, $secret);
    $stack->push($authenticationMiddleware);
    $client = new Client(['base_uri' => 'https://api.cmpayments.com/', 'handler' => $stack]);

    $response = $client->request('POST', 'charges/v1', [
        'json' => $body
    ]);
```

Below an example in order to request the iDEAL-issuers list via a GET-request.

```php
<?php
    $key = 'Your-OAuth-Consumer-Key';
    $secret = 'Your-OAuth-Consumer-Secret';

    $stack = HandlerStack::create();
    $authenticationMiddleware = new AuthenticationMiddleware($key, $secret);
    $stack->push($authenticationMiddleware);
    $client = new Client(['base_uri' => 'https://api.cmpayments.com/', 'handler' => $stack]);

    $response = $client->request('GET', 'issuers/v1/ideal', []);
```



## Submitting bugs and feature requests

Bugs and feature request are tracked on [GitHub](https://github.com/cmpayments/guzzle-psp-authentication-middleware/issues)

## Copyright and license

The cmpayment/guzzle-psp-authentication-middleware library is copyright © [CM Payments](https://cmpayments.com/) and licensed for use under the MIT License (MIT). Please see [LICENSE][] for more information.

[badge-build]: https://img.shields.io/travis/cmpayments/guzzle-psp-authentication-middleware.svg?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/cmpayments/guzzle-psp-authentication-middleware.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/cmpayments/guzzle-psp-authentication-middleware.svg?style=flat-square
[badge-coverage]: https://scrutinizer-ci.com/g/cmpayments/guzzle-psp-authentication-middleware/badges/coverage.png?b=master

[license]: https://github.com/cmpayments/guzzle-psp-authentication-middleware/blob/master/LICENSE
[build]: https://travis-ci.org/cmpayments/guzzle-psp-authentication-middleware
[quality]: https://scrutinizer-ci.com/g/cmpayments/guzzle-psp-authentication-middleware/
[coverage]: https://scrutinizer-ci.com/g/cmpayments/guzzle-psp-authentication-middleware/?branch=master
[downloads]: https://packagist.org/packages/cmpayments/guzzle-psp-authentication-middleware


