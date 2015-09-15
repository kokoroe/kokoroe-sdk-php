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
            'default_api_version'   => 'v1.2'
        ]);

        $this->assertEquals('v1.2', $kokoroe->getDefaultApiVersion());
        $this->assertEquals('171b379e-57cd-11e5-aea8-eb2b3eb94fb9', $kokoroe->getClientId());
        $this->assertEquals('foo', $kokoroe->getClientSecret());
        $this->assertEquals('bar', $kokoroe->getDefaultAccessToken());
        $this->assertEquals(Kokoroe::BASE_API_URL . '/v1.2', $kokoroe->getBaseApiUrl());
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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $clientMock->method('get')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo([
                    'Authorization' => 'Bearer foo'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo'
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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $clientMock->method('get')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo([
                    'Authorization' => 'Bearer bar'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo'
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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $clientMock->method('get')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([
                    'fields' => 'email'
                ]),
                $this->equalTo([
                    'Authorization' => 'Basic MTcxYjM3OWUtNTdjZC0xMWU1LWFlYTgtZWIyYjNlYjk0ZmI5Og=='
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo'
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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $kokoroe->get('/me');
    }

    public function testPostWithAccessToken()
    {
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $clientMock->method('post')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo('bar'),
                $this->equalTo([
                    'Authorization' => 'Bearer foo'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->post('/me', 'bar', 'foo');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }

    public function testPutWithAccessToken()
    {
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $clientMock->method('put')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo('bar'),
                $this->equalTo([
                    'Authorization' => 'Bearer foo'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo'
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
        $clientMock = $this->getMock('Kokoroe\Http\Client');

        $clientMock->method('delete')
            ->with(
                $this->equalTo(Kokoroe::BASE_API_URL . '/' . Kokoroe::DEFAULT_API_VERSION . '/me'),
                $this->equalTo([]),
                $this->equalTo([
                    'Authorization' => 'Bearer foo'
                ])
            )
            ->will($this->returnValue([
                'id' => 1234,
                'email' => 'name@domain.tld'
            ]));

        $kokoroe = new Kokoroe([
            'client_id' => '171b379e-57cd-11e5-aea8-eb2b3eb94fb9',
            'client_secret' => 'foo'
        ]);
        $kokoroe->setHttpClient($clientMock);

        $response = $kokoroe->delete('/me', 'foo');

        $this->assertEquals([
            'id' => 1234,
            'email' => 'name@domain.tld'
        ], $response);
    }
}
