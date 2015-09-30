<?php
/*
 * This file is part of the kokoroe-sdk-php.
 *
 * (c) I Know U Will SAS <open@kokoroe.co>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @namespace
 */
namespace Kokoroe\Tests\Http;

use Kokoroe\Http\Client;
use UnexpectedValueException;

/**
 * Client Test
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultAdapter()
    {
        $client = new Client();

        $this->assertInstanceOf('\Kokoroe\Http\Client\Adapter\Curl', $client->getAdapter());
    }

    public function testAddingAdapter()
    {
        $adapterMock = $this->getMock('\Kokoroe\Http\Client\Adapter\AdapterInterface');

        $client = new Client();
        $client->setAdapter($adapterMock);
        $this->assertInstanceOf('\Kokoroe\Http\Client\Adapter\AdapterInterface', $client->getAdapter());
        $this->assertEquals($adapterMock, $client->getAdapter());
    }

    public function testSendRequest()
    {
        $adapterMock = $this->getMock('\Kokoroe\Http\Client\Adapter\AdapterInterface');
        $adapterMock->method('send')
            ->with($this->equalTo('GET'), $this->equalTo('https://api.kokoroe.co/v1.0/me?access_token=foo'));

        $client = new Client();
        $client->setAdapter($adapterMock);

        $client->send('GET', 'https://api.kokoroe.co/v1.0/me', [
            'access_token' => 'foo'
        ]);
    }

    public function testSendGetRequest()
    {
        $adapterMock = $this->getMock('\Kokoroe\Http\Client\Adapter\AdapterInterface');
        $adapterMock->method('send')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo('https://api.kokoroe.co/v1.0/me?access_token=foo'),
                $this->equalTo(null),
                [
                    'Accept-Language' => 'fr-FR'
                ]
            );

        $client = new Client();
        $client->setAdapter($adapterMock);

        $client->get('https://api.kokoroe.co/v1.0/me', [
            'access_token' => 'foo'
        ], [
            'Accept-Language' => 'fr-FR'
        ]);
    }

    public function testSendPostRequest()
    {
        $adapterMock = $this->getMock('\Kokoroe\Http\Client\Adapter\AdapterInterface');
        $adapterMock->method('send')
            ->with(
                $this->equalTo('POST'),
                $this->equalTo('https://api.kokoroe.co/v1.0/me?access_token=foo'),
                $this->equalTo('bar'),
                [
                    'Accept-Language' => 'fr-FR'
                ]
            );

        $client = new Client();
        $client->setAdapter($adapterMock);

        $client->post('https://api.kokoroe.co/v1.0/me', [
            'access_token' => 'foo'
        ], 'bar', [
            'Accept-Language' => 'fr-FR'
        ]);
    }

    public function testSendPutRequest()
    {
        $adapterMock = $this->getMock('\Kokoroe\Http\Client\Adapter\AdapterInterface');
        $adapterMock->method('send')
            ->with(
                $this->equalTo('PUT'),
                $this->equalTo('https://api.kokoroe.co/v1.0/me?access_token=foo'),
                $this->equalTo('bar'),
                [
                    'Accept-Language' => 'fr-FR'
                ]
            );

        $client = new Client();
        $client->setAdapter($adapterMock);

        $client->put('https://api.kokoroe.co/v1.0/me', [
            'access_token' => 'foo'
        ], 'bar', [
            'Accept-Language' => 'fr-FR'
        ]);
    }

    public function testSendDeleteRequest()
    {
        $adapterMock = $this->getMock('\Kokoroe\Http\Client\Adapter\AdapterInterface');
        $adapterMock->method('send')
            ->with(
                $this->equalTo('DELETE'),
                $this->equalTo('https://api.kokoroe.co/v1.0/me?access_token=foo'),
                $this->equalTo(null),
                [
                    'Accept-Language' => 'fr-FR'
                ]
            );

        $client = new Client();
        $client->setAdapter($adapterMock);

        $client->delete('https://api.kokoroe.co/v1.0/me', [
            'access_token' => 'foo'
        ], [
            'Accept-Language' => 'fr-FR'
        ]);
    }
}
