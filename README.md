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

### Example

~~~php
<?php

$kokoroe = new Kokoroe\Kokoroe([
    'client_id'             => '{client-id}',
    'client_secret'         => '{client-secret}',
    'country'               => 'FR',
    'locale'                => 'fr',
    'user_ip'               => $_SERVER['REMOTE_ADDR'], // use real ip of user.
    'default_access_token'  => '{access-token}', // optional
    'signature'             => true // optional
]);

// If you provided a 'default_access_token', the '{access-token}' is optional.
$response = $kokoroe->get('/me', '{access-token}');

if ($response->isSuccessful()) {
    var_dump($response->getContent()); // dump array
}

?>
~~~

### Options


| Name                 | Type   | Default                | Description                                            | Required |
| -------------------- | ------ | ---------------------- | ------------------------------------------------------ | -------- |
| client_id            | string | null                   | The id of your application, Format: UUID.              | yes      |
| client_secret        | string | null                   | The secret key of yout application.                    | yes      |
| user_ip              | string | null                   | The IP address of user.                                | yes      |
| country              | strung | null                   | The country code, Ex: FR.                              | yes      |
| default_access_token | string | null                   | The default access_token.                              | no       |
| default_api_version  | string | v1.0                   | The default API version.                               | no       |
| default_api_url      | string | https://api.kokoroe.co | The default API url.                                   | no       |
| locale               | string | en                     | The locale of response, Ex: en                         | no       |
| ssl_verify           | bool   | true                   | Enable or disable the verification of SSL certificate. | no       |
| tracker              | string | null                   | The Tracker-ID for identifie request.                  | no       |
| signature            | bool   | null                   | Enable or disable the signature of requests.           | no       |


## License

kokoroe-sdk-php is licensed under [the MIT license](LICENSE.md).
