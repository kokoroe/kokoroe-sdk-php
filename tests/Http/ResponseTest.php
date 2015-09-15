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

/**
 * Response Test
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    protected $response;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testStatusCodeIs200ByDefault()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testStatusCodeMutatorReturnsCloneWithChanges()
    {
        $response = $this->response->withStatus(400);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testProtocolHasAcceptableDefault()
    {
        $this->assertEquals('1.1', $this->response->getProtocolVersion());
    }

    public function testProtocolMutatorReturnsCloneWithChanges()
    {
        $response = $this->response->withProtocolVersion('1.0');
        $this->assertNotSame($this->response, $response);
        $this->assertEquals('1.0', $response->getProtocolVersion());
    }

    public function invalidStatusCodes()
    {
        return [
            'too-low'   => [99],
            'too-high'  => [600],
            'null'      => [null],
            'bool'      => [true],
            'string'    => ['foo'],
            'array'     => [[200]],
            'object'    => [(object) [200]]
        ];
    }

    /**
     * @dataProvider invalidStatusCodes
     */
    public function testCannotSetInvalidStatusCode($code)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->response->withStatus($code);
    }

    public function testReasonPhraseDefaultsToStandards()
    {
        $response = $this->response->withStatus(422);
        $this->assertEquals('Unprocessable Entity', $response->getReasonPhrase());
    }

    public function testCanSetCustomReasonPhrase()
    {
        $response = $this->response->withStatus(422, 'Foo Bar!');
        $this->assertEquals('Foo Bar!', $response->getReasonPhrase());
    }

    public function testReasonPhraseCanBeEmpty()
    {
        $response = $this->response->withStatus(599);
        $this->assertInternalType('string', $response->getReasonPhrase());
        $this->assertEmpty($response->getReasonPhrase());
    }

    public function testBodyMutatorReturnsCloneWithChanges()
    {
        $stream  = $this->getMock('Psr\Http\Message\StreamInterface');
        $response = $this->response->withBody($stream);
        $this->assertNotSame($this->response, $response);
        $this->assertSame($stream, $response->getBody());
    }

    public function testGetHeaderReturnsHeaderValueAsArray()
    {
        $response = $this->response->withHeader('X-Foo', ['Foo', 'Bar']);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals(['Foo', 'Bar'], $response->getHeader('X-Foo'));
    }

    public function testGetHeaderLineReturnsHeaderValueAsCommaConcatenatedString()
    {
        $response = $this->response->withHeader('X-Foo', ['Foo', 'Bar']);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals('Foo,Bar', $response->getHeaderLine('X-Foo'));
    }

    public function testGetHeadersKeepsHeaderCaseSensitivity()
    {
        $response = $this->response->withHeader('X-Foo', ['Foo', 'Bar']);
        $this->assertNotSame($this->response, $response);
        $this->assertEquals([ 'X-Foo' => [ 'Foo', 'Bar' ] ], $response->getHeaders());
    }

    public function testGetHeadersReturnsCaseWithWhichHeaderFirstRegistered()
    {
        $response = $this->response
            ->withHeader('X-Foo', 'Foo')
            ->withAddedHeader('x-foo', 'Bar');
        $this->assertNotSame($this->response, $response);
        $this->assertEquals([ 'X-Foo' => [ 'Foo', 'Bar' ] ], $response->getHeaders());
    }

    public function testHasHeaderReturnsFalseIfHeaderIsNotPresent()
    {
        $this->assertFalse($this->response->hasHeader('X-Foo'));
    }

    public function testHasHeaderReturnsTrueIfHeaderIsPresent()
    {
        $response = $this->response->withHeader('X-Foo', 'Foo');
        $this->assertNotSame($this->response, $response);
        $this->assertTrue($response->hasHeader('X-Foo'));
    }

    public function testAddHeaderAppendsToExistingHeader()
    {
        $response  = $this->response->withHeader('X-Foo', 'Foo');
        $this->assertNotSame($this->response, $response);
        $response2 = $response->withAddedHeader('X-Foo', 'Bar');
        $this->assertNotSame($response, $response2);
        $this->assertEquals('Foo,Bar', $response2->getHeaderLine('X-Foo'));
    }

    public function testCanRemoveHeaders()
    {
        $response = $this->response->withHeader('X-Foo', 'Foo');
        $this->assertNotSame($this->response, $response);
        $this->assertTrue($response->hasHeader('x-foo'));
        $response2 = $response->withoutHeader('x-foo');
        $this->assertNotSame($this->response, $response2);
        $this->assertNotSame($response, $response2);
        $this->assertFalse($response2->hasHeader('X-Foo'));
    }

    public function testHeaderRemovalIsCaseInsensitive()
    {
        $response = $this->response
            ->withHeader('X-Foo', 'Foo')
            ->withAddedHeader('x-foo', 'Bar')
            ->withAddedHeader('X-FOO', 'Baz');
        $this->assertNotSame($this->response, $response);
        $this->assertTrue($response->hasHeader('x-foo'));
        $response2 = $response->withoutHeader('x-foo');
        $this->assertNotSame($this->response, $response2);
        $this->assertNotSame($response, $response2);
        $this->assertFalse($response2->hasHeader('X-Foo'));
        $headers = $response2->getHeaders();
        $this->assertEquals(0, count($headers));
    }

    public function invalidGeneralHeaderValues()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'array'  => [[ 'foo' => [ 'bar' ] ]],
            'object' => [(object) [ 'foo' => 'bar' ]],
        ];
    }

    /**
     * @dataProvider invalidGeneralHeaderValues
     */
    public function testWithHeaderRaisesExceptionForInvalidNestedHeaderValue($value)
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid header value');
        $this->response->withHeader('X-Foo', [ $value ]);
    }

    public function invalidHeaderValues()
    {
        return [
            'null'   => [null],
            'true'   => [true],
            'false'  => [false],
            'int'    => [1],
            'float'  => [1.1],
            'object' => [(object) [ 'foo' => 'bar' ]],
        ];
    }

    /**
     * @dataProvider invalidHeaderValues
     */
    public function testWithHeaderRaisesExceptionForInvalidValueType($value)
    {
        $this->setExpectedException('InvalidArgumentException', 'Invalid header value');
        $this->response->withHeader('X-Foo', $value);
    }

    /**
     * @dataProvider invalidGeneralHeaderValues
     */
    public function testWithAddedHeaderRaisesExceptionForNonStringNonArrayValue($value)
    {
        $this->setExpectedException('InvalidArgumentException', 'must be a string');
        $this->response->withAddedHeader('X-Foo', $value);
    }

    public function testWithoutHeaderDoesNothingIfHeaderDoesNotExist()
    {
        $this->assertFalse($this->response->hasHeader('X-Foo'));
        $response = $this->response->withoutHeader('X-Foo');
        $this->assertNotSame($this->response, $response);
        $this->assertFalse($response->hasHeader('X-Foo'));
    }

    public function testGetHeaderReturnsAnEmptyArrayWhenHeaderDoesNotExist()
    {
        $this->assertSame([], $this->response->getHeader('X-Foo-Bar'));
    }

    public function testGetHeaderLineReturnsEmptyStringWhenHeaderDoesNotExist()
    {
        $this->assertEmpty($this->response->getHeaderLine('X-Foo-Bar'));
    }

    public function headersWithInjectionVectors()
    {
        return [
            'name-with-cr'           => ["X-Foo\r-Bar", 'value'],
            'name-with-lf'           => ["X-Foo\n-Bar", 'value'],
            'name-with-crlf'         => ["X-Foo\r\n-Bar", 'value'],
            'name-with-2crlf'        => ["X-Foo\r\n\r\n-Bar", 'value'],
            'value-with-cr'          => ['X-Foo-Bar', "value\rinjection"],
            'value-with-lf'          => ['X-Foo-Bar', "value\ninjection"],
            'value-with-crlf'        => ['X-Foo-Bar', "value\r\ninjection"],
            'value-with-2crlf'       => ['X-Foo-Bar', "value\r\n\r\ninjection"],
            'array-value-with-cr'    => ['X-Foo-Bar', ["value\rinjection"]],
            'array-value-with-lf'    => ['X-Foo-Bar', ["value\ninjection"]],
            'array-value-with-crlf'  => ['X-Foo-Bar', ["value\r\ninjection"]],
            'array-value-with-2crlf' => ['X-Foo-Bar', ["value\r\n\r\ninjection"]],
        ];
    }

    /**
     * @dataProvider headersWithInjectionVectors
     */
    public function testDoesNotAllowCRLFInjectionWhenCallingWithHeader($name, $value)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->response->withHeader($name, $value);
    }

    /**
     * @dataProvider headersWithInjectionVectors
     */
    public function testDoesNotAllowCRLFInjectionWhenCallingWithAddedHeader($name, $value)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->response->withAddedHeader($name, $value);
    }

    public function testWithHeaderAllowsHeaderContinuations()
    {
        $response = $this->response->withHeader('X-Foo-Bar', "value,\r\n second value");
        $this->assertEquals("value,\r\n second value", $response->getHeaderLine('X-Foo-Bar'));
    }

    public function testWithAddedHeaderAllowsHeaderContinuations()
    {
        $response = $this->response->withAddedHeader('X-Foo-Bar', "value,\r\n second value");
        $this->assertEquals("value,\r\n second value", $response->getHeaderLine('X-Foo-Bar'));
    }
}
