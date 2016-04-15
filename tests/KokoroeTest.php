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
namespace Kokoroe\Tests;

use Kokoroe\Kokoroe;

/**
 * Kokoroe Test
 */
class KokoroeTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWithoutOptions()
    {
        $kokoroe = new Kokoroe();

        $this->assertEquals(Kokoroe::DEFAULT_API_VERSION, $kokoroe->getDefaultApiVersion());
        $this->assertEquals(
            Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION,
            $kokoroe->getBaseApiUrl()
        );
    }

    public function testConstructorWithOptions()
    {
        $kokoroe = new Kokoroe([
            'client_id'             => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret'         => 'foo',
            'default_access_token'  => 'bar',
            'default_api_version'   => 'v1.2',
            'default_api_url'       => 'https://test.kokoroe.co',
            'locale'                => 'fr',
            'country'               => 'FR',
            'ssl_verify'            => false,
            'user_ip'               => '164.177.100.111',
            'tracker'               => '99f917d8-7999-11e5-b03a-c3d62dc040e2'
        ]);

        $this->assertEquals('v1.2', $kokoroe->getDefaultApiVersion());
        $this->assertEquals('171b379e-57cd-11e5-aea8-eb2b3eb94fb9', $kokoroe->getClientId());
        $this->assertEquals('foo', $kokoroe->getClientSecret());
        $this->assertEquals('bar', $kokoroe->getDefaultAccessToken());
        $this->assertEquals($kokoroe->getDefaultApiUrl() . '/v1.2', $kokoroe->getBaseApiUrl());
        $this->assertFalse($kokoroe->getSslVerify());
        $this->assertEquals('fr', $kokoroe->getLocale());
        $this->assertEquals('FR', $kokoroe->getCountry());
        $this->assertEquals('164.177.100.111', $kokoroe->getUserIp());
        $this->assertEquals('99f917d8-7999-11e5-b03a-c3d62dc040e2', $kokoroe->getTracker());
    }

    /**
     * @expectedException Kokoroe\Exception
     */
    public function testConstructorWithBadClientId()
    {
        (new Kokoroe([
            'client_id' => '171dsfgdfgdf9'
        ]));
    }

    public function testClientHttp()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $kokoroe = new Kokoroe();
        $kokoroe->setHttpClient($clientMock);

        $this->assertEquals($clientMock, $kokoroe->getHttpClient());
    }

    public function testDefaultClientHttp()
    {
        $kokoroe = new Kokoroe();
        $this->assertInstanceOf('Kokoroe\Http\Client', $kokoroe->getHttpClient());
    }

    public function testGetWithAccessToken()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('get')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo([
                    'Authorization'     => 'Bearer foo',
                    'Accept-Language'   => 'en',
                    'X-Forwarded-For'   => '164.177.100.111',
                    'X-Kokoroe-Tracker' => '99f917d8-7999-11e5-b03a-c3d62dc040e2',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id'    => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR',
            'user_ip'       => '164.177.100.111',
            'tracker'       => '99f917d8-7999-11e5-b03a-c3d62dc040e2'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->get('/me', 'foo');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    public function testGetWithDefaultAccessToken()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('get')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo([
                    'Authorization'     => 'Bearer bar',
                    'Accept-Language'   => 'en',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR'
        ]);
        $kokoroe->setHttpClient($clientMock);
        $kokoroe->setDefaultAccessToken('bar');

        $response = $kokoroe->get('me');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    public function testGetWithoutAccessToken()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('get')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([
                    'fields' => 'email'
                ]),
                $this->equalTo([
                    'Authorization'     => 'Basic MTcxYjM3OWUtNTdjZC0xMWU1LWFlYTgtZWIyYjNlYjk0ZmI5Og==',
                    'Accept-Language'   => 'en',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->get('/me?fields=email');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    /**
     * @expectedException Kokoroe\Exception
     * @expectedExceptionMessage Required "client_id" key not supplied in options
     */
    public function testGetWithoutClientId()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $kokoroe = new Kokoroe();
        $kokoroe->setHttpClient($clientMock);

        $kokoroe->get('/me');
    }

    /**
     * @expectedException Kokoroe\Exception
     * @expectedExceptionMessage Required "client_secret" key not supplied in options
     */
    public function testGetWithoutClientSecret()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $loggerMock = $this->getMock('Psr\Log\LoggerInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->expects($this->once())
            ->method('setLogger')
            ->with($this->equalTo($loggerMock));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9'
        ]);
        $kokoroe->setLogger($loggerMock);
        $kokoroe->setHttpClient($clientMock);

        $kokoroe->get('/me');
    }

    public function testPostWithAccessToken()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('post')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo('bar'),
                $this->equalTo([
                    'Authorization'     => 'Bearer foo',
                    'Accept-Language'   => 'en',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->post('/me', 'bar', 'foo');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    public function testGetWithSignature()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('post')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo('bar'),
                $this->equalTo([
                    'Authorization'     => 'Bearer foo',
                    'Accept-Language'   => 'en',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR',
            'signature'     => true
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->post('/me', 'bar', 'foo');

        $this->assertTrue($kokoroe->hasSignature());

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);

        $signatureMock = $this->getMock('Kokoroe\Http\Signature\SignatureInterface');
        $signatureMock->expects($this->once())
            ->method('setKey')
            ->with($this->equalTo('foo'));

        $clientMock->expects($this->once())
            ->method('setSignature')
            ->with($this->equalTo($signatureMock));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR',
            'signature'     => $signatureMock
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->post('/me', 'bar', 'foo');

        $this->assertTrue($kokoroe->hasSignature());
        $this->assertEquals($signatureMock, $kokoroe->getSignature());

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    public function testPutWithAccessToken()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('put')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo('bar'),
                $this->equalTo([
                    'Authorization'     => 'Bearer foo',
                    'Accept-Language'   => 'en',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->put('/me', 'bar', 'foo');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    public function testDeleteWithAccessToken()
    {
        $adapterMock = $this->getMock('Kokoroe\Http\Client\Adapter\AdapterInterface');

        $clientMock = $this->getMock('Kokoroe\Http\Client');
        $clientMock->method('getAdapter')
            ->will($this->returnValue($adapterMock));

        $clientMock->method('delete')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo([
                    'Authorization'     => 'Bearer foo',
                    'Accept-Language'   => 'en',
                    'X-Country'         => 'FR'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id'     => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo',
            'country'       => 'FR'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->delete('/me', 'foo');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }
}
