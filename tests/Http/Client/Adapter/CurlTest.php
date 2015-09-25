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
namespace Kokoroe\Tests\Http\Client\Adapter;

use Kokoroe\Http\Client\Adapter\Curl;

/**
 * Curl Test
 */
class CurlTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!extension_loaded('curl')) {
            $this->markTestSkipped('cURL is not installed, marking all Http Client Curl Adapter tests skipped.');
        }
    }

    public function testConstructor()
    {
        $curl = new Curl();
        $this->assertInstanceOf('Kokoroe\Http\Client\Adapter\AdapterInterface', $curl);
    }

    public function testSslVerify()
    {
        $curl = new Curl();
        $this->assertTrue($curl->isSslVerify());

        $curl->setSslVerify(false);

        $this->assertFalse($curl->isSslVerify());
    }

    public function testSendGetRequestWithoutSslVerify()
    {
        $curl = new Curl();
        $curl->setSslVerify(false);

        $response = $curl->send('GET', 'http://localhost:1337/?access_token=foo', null, [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"access_token":"foo"}', (string) $response->getBody());
    }

    public function testSendGetRequest()
    {
        $curl = new Curl();

        $response = $curl->send('GET', 'http://localhost:1337/?access_token=foo', null, [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"access_token":"foo"}', (string) $response->getBody());
    }

    public function testSendHeadRequest()
    {
        $curl = new Curl();
        $response = $curl->send('HEAD', 'http://localhost:1337/?access_token=foo', null, [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('', (string) $response->getBody());
    }

    public function testSendPostRequest()
    {
        $curl = new Curl();
        $response = $curl->send('POST', 'http://localhost:1337/?access_token=foo', ['foo' => 'bar'], [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string) $response->getBody());
    }

    public function testSendPutRequest()
    {
        $curl = new Curl();
        $response = $curl->send('PUT', 'http://localhost:1337/?access_token=foo', ['foo' => 'bar'], [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"foo":"bar"}', (string) $response->getBody());
    }

    public function testSendDeleteRequest()
    {
        $curl = new Curl();
        $response = $curl->send('DELETE', 'http://localhost:1337/?access_token=foo', null, [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"access_token":"foo"}', (string) $response->getBody());
    }

    public function testRedirectRequest()
    {
        $curl = new Curl();

        $response = $curl->send('GET', 'http://localhost:1337/redirect?access_token=foo', null, [
            'Accept' => 'application/json'
        ], 2);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"access_token":"foo"}', (string) $response->getBody());
    }
}
