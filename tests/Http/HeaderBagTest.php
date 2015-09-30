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

use Kokoroe\Http\HeaderBag;

/**
 * HeaderBag Test
 */
class HeaderBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Kokoroe\Http\HeaderBag::__construct
     */
    public function testConstructor()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        $this->assertTrue($bag->has('foo'));
    }

    public function testKeys()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        $keys = $bag->keys();
        $this->assertEquals('foo', $keys[0]);
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::all
     */
    public function testAll()
    {
        $bag = new HeaderBag(['foo' => 'bar']);
        $this->assertEquals(['foo' => ['bar']], $bag->all(), '->all() gets all the input');

        $bag = new HeaderBag(['FOO' => 'BAR']);
        $this->assertEquals(['foo' => ['BAR']], $bag->all(), '->all() gets all the input key are lower case');
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::replace
     */
    public function testReplace()
    {
        $bag = new HeaderBag(['foo' => 'bar']);

        $bag->replace(['NOPE' => 'BAR']);
        $this->assertEquals(['nope' => ['BAR']], $bag->all(), '->replace() replaces the input with the argument');
        $this->assertFalse($bag->has('foo'), '->replace() overrides previously set the input');
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::add
     */
    public function testAdd()
    {
        $bag = new HeaderBag();
        $bag->add(['foo' => 'bar', 'fuzz' => 'bizz']);
        $this->assertEquals('bar', $bag->get('foo'), '->get return current value');
        $this->assertEquals('bar', $bag->get('FoO'), '->get key in case insensitive');
        $this->assertEquals(['bar'], $bag->get('foo', 'nope', false), '->get return the value as array');

        // defaults
        $this->assertNull($bag->get('none'), '->get unknown values returns null');
        $this->assertEquals('default', $bag->get('none', 'default'), '->get unknown values returns default');
        $this->assertEquals(['default'], $bag->get('none', 'default', false), '->get unknown values returns default as array');

        $bag->set('foo', 'bor', false);
        $this->assertEquals('bar', $bag->get('foo'), '->get return first value');
        $this->assertEquals(['bar', 'bor'], $bag->get('foo', 'nope', false), '->get return all values as array');
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::has
     */
    public function testHas()
    {
        $bag = new HeaderBag(['X-Limit' => '10', 'fuzz' => 'bizz']);

        $this->assertTrue($bag->has('x-limit'));
        $this->assertTrue($bag->has('X-Limit'));
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::remove
     */
    public function testRemove()
    {
        $bag = new HeaderBag(['X-Limit' => '10', 'fuzz' => 'bizz']);

        $this->assertTrue($bag->has('x-limit'));

        $bag->remove('x-limit');

        $this->assertFalse($bag->has('x-limit'));
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::get
     */
    public function testGet()
    {
        $bag = new HeaderBag(['foo' => 'bar', 'fuzz' => 'bizz']);
        $this->assertEquals('bar', $bag->get('foo'), '->get return current value');
        $this->assertEquals('bar', $bag->get('FoO'), '->get key in case insensitive');
        $this->assertEquals(['bar'], $bag->get('foo', 'nope', false), '->get return the value as array');

        // defaults
        $this->assertNull($bag->get('none'), '->get unknown values returns null');
        $this->assertEquals('default', $bag->get('none', 'default'), '->get unknown values returns default');
        $this->assertEquals(['default'], $bag->get('none', 'default', false), '->get unknown values returns default as array');

        $bag->set('foo', 'bor', false);
        $this->assertEquals('bar', $bag->get('foo'), '->get return first value');
        $this->assertEquals(['bar', 'bor'], $bag->get('foo', 'nope', false), '->get return all values as array');
    }

    public function testSetAssociativeArray()
    {
        $bag = new HeaderBag();
        $bag->set('foo', ['bad-assoc-index' => 'value']);
        $this->assertSame('value', $bag->get('foo'));
        $this->assertEquals(['value'], $bag->get('foo', 'nope', false), 'assoc indices of multi-valued headers are ignored');
        $bag->set('foo', ['bar' => 'val'], false);
        $this->assertSame('value', $bag->get('foo'));
        $this->assertEquals(['value', 'val'], $bag->get('foo', 'nope', false), 'assoc indices of multi-valued headers are ignored');

    }

    /**
     * @covers Kokoroe\Http\HeaderBag::contains
     */
    public function testContains()
    {
        $bag = new HeaderBag(['foo' => 'bar', 'fuzz' => 'bizz']);
        $this->assertTrue($bag->contains('foo', 'bar'), '->contains first value');
        $this->assertTrue($bag->contains('fuzz', 'bizz'), '->contains second value');
        $this->assertFalse($bag->contains('nope', 'nope'), '->contains unknown value');
        $this->assertFalse($bag->contains('foo', 'nope'), '->contains unknown value');

        // Multiple values
        $bag->set('foo', 'bor', false);
        $this->assertTrue($bag->contains('foo', 'bar'), '->contains first value');
        $this->assertTrue($bag->contains('foo', 'bor'), '->contains second value');
        $this->assertFalse($bag->contains('foo', 'nope'), '->contains unknown value');
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::getIterator
     */
    public function testGetIterator()
    {
        $headers = ['foo' => 'bar', 'hello' => 'world', 'third' => 'charm'];
        $headerBag = new HeaderBag($headers);

        $i = 0;
        foreach ($headerBag as $key => $val) {
            ++$i;
            $this->assertEquals([$headers[$key]], $val);
        }

        $this->assertEquals(count($headers), $i);
    }

    /**
     * @covers Kokoroe\Http\HeaderBag::count
     */
    public function testCount()
    {
        $headers = ['foo' => 'bar', 'HELLO' => 'WORLD'];
        $headerBag = new HeaderBag($headers);

        $this->assertEquals(count($headers), count($headerBag));
    }
}
