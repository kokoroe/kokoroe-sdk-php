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

use Kokoroe\Http\Response;
use ReflectionProperty;

/**
 * Response Test
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testIsSuccessful()
    {
        $response = new Response();
        $this->assertTrue($response->isSuccessful());
    }

    /**
     * @dataProvider getStatusCodeFixtures
     */
    public function testSetStatusCode($code, $text, $expectedText)
    {
        $response = new Response();

        $response->setStatusCode($code, $text);

        $statusText = new ReflectionProperty($response, 'statusText');
        $statusText->setAccessible(true);

        $this->assertEquals($expectedText, $statusText->getValue($response));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetStatusCodeWithBadCode()
    {
        $response = new Response();

        $response->setStatusCode(1337);
    }

    public function getStatusCodeFixtures()
    {
        return [
            ['200', null, 'OK'],
            ['200', false, ''],
            ['200', 'foo', 'foo'],
            ['199', null, ''],
            ['199', false, ''],
            ['199', 'foo', 'foo'],
        ];
    }


    public function testIsInformational()
    {
        $response = new Response('', 100);
        $this->assertTrue($response->isInformational());

        $response = new Response('', 200);
        $this->assertFalse($response->isInformational());
    }

    public function testIsRedirectRedirection()
    {
        foreach (array(301, 302, 303, 307) as $code) {
            $response = new Response('', $code);
            $this->assertTrue($response->isRedirection());
            $this->assertTrue($response->isRedirect());
        }

        $response = new Response('', 304);
        $this->assertTrue($response->isRedirection());
        $this->assertFalse($response->isRedirect());

        $response = new Response('', 200);
        $this->assertFalse($response->isRedirection());
        $this->assertFalse($response->isRedirect());

        $response = new Response('', 404);
        $this->assertFalse($response->isRedirection());
        $this->assertFalse($response->isRedirect());

        $response = new Response('', 301, array('Location' => '/good-uri'));
        $this->assertFalse($response->isRedirect('/bad-uri'));
        $this->assertTrue($response->isRedirect('/good-uri'));
    }

    public function testIsNotFound()
    {
        $response = new Response('', 404);
        $this->assertTrue($response->isNotFound());

        $response = new Response('', 200);
        $this->assertFalse($response->isNotFound());
    }

    public function testIsEmpty()
    {
        foreach (array(204, 304) as $code) {
            $response = new Response('', $code);
            $this->assertTrue($response->isEmpty());
        }

        $response = new Response('', 200);
        $this->assertFalse($response->isEmpty());
    }

    public function testIsForbidden()
    {
        $response = new Response('', 403);
        $this->assertTrue($response->isForbidden());

        $response = new Response('', 200);
        $this->assertFalse($response->isForbidden());
    }

    public function testIsOk()
    {
        $response = new Response('', 200);
        $this->assertTrue($response->isOk());

        $response = new Response('', 404);
        $this->assertFalse($response->isOk());
    }

    public function testIsServerOrClientError()
    {
        $response = new Response('', 404);
        $this->assertTrue($response->isClientError());
        $this->assertFalse($response->isServerError());

        $response = new Response('', 500);
        $this->assertFalse($response->isClientError());
        $this->assertTrue($response->isServerError());
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage The Response content must be a string or object implementing __toString(), "object" given.
     */
    public function testBadSetContent()
    {
        $response = new Response();
        $response->setContent(new \stdClass);
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Syntax error
     */
    public function testBadJsonResponse()
    {
        $response = new Response('{"foo":"bar"', 200, [
            'Content-Type' => 'application/json'
        ]);
        $response->getContent();
    }

    public function testJsonResponse()
    {
        $response = new Response('{"foo":"bar"}', 200, [
            'Content-Type' => 'application/json'
        ]);
        $this->assertEquals(['foo' => 'bar'], $response->getContent());

        $response = new Response('{"foo":"bar"}');
        $this->assertEquals('{"foo":"bar"}', $response->getContent());
    }
}
