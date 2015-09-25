# Kokoroe SDK PHP

[![Build Status](https://img.shields.io/travis/kokoroe/kokoroe-sdk-php/master.svg)](https://travis-ci.org/kokoroe/kokoroe-sdk-php)
[![SensioLabs Insight](https://img.shields.io/sensiolabs/i/8d361bb3-7b77-4888-87f4-e72f29dd9d18.svg)](https://insight.sensiolabs.com/projects/8d361bb3-7b77-4888-87f4-e72f29dd9d18)
[![Coveralls](https://img.shields.io/coveralls/kokoroe/kokoroe-sdk-php.svg)](https://coveralls.io/github/kokoroe/kokoroe-sdk-php)
[![HHVM](https://img.shields.io/hhvm/kokoroe/kokoroe-sdk-php.svg)](https://travis-ci.org/kokoroe/kokoroe-sdk-php)
[![Packagist](https://img.shields.io/packagist/v/kokoroe/kokoroe-sdk-php.svg)](https://packagist.org/packages/kokoroe/kokoroe-sdk-php)

## Install

Add `kokoroe/kokoroe-sdk-php` to your `composer.json`:

    % php composer.phar require kokoroe/kokoroe-sdk-php:~1.0

## Usage

### Configuration

```php
<?php

$kokoroe = new Kokoroe\Kokoroe([
    'client_id'             => '{client-id}',
    'client_secret'         => '{client-secret}',
    'default_access_token'  => '{access-token}' // optional
]);

// If you provided a 'default_access_token', the '{access-token}' is optional.
$user = $kokoroe->get('/me', '{access-token}');
```

## License

kokoroe-sdk-php is licensed under [the MIT license](LICENSE.md).
