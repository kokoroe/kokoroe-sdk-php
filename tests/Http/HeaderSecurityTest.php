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

use Kokoroe\Http\HeaderSecurity;

/**
 * Header Security Test
 */
class HeaderSecurityTest extends \PHPUnit_Framework_TestCase
{
    public function validateValues()
    {
        return [
            ["This is a\n test", 'assertFalse'],
            ["This is a\r test", 'assertFalse'],
            ["This is a\n\r test", 'assertFalse'],
            ["This is a\r\n  test", 'assertTrue'],
            ["This is a \r\ntest", 'assertFalse'],
            ["This is a \r\n\n test", 'assertFalse'],
            ["This is a\n\n test", 'assertFalse'],
            ["This is a\r\r test", 'assertFalse'],
            ["This is a \r\r\n test", 'assertFalse'],
            ["This is a \r\n\r\ntest", 'assertFalse'],
            ["This is a \r\n\n\r\n test", 'assertFalse'],
            ["This is a \xFF test", 'assertFalse'],
            ["This is a \x7F test", 'assertFalse'],
            ["This is a \x7E test", 'assertTrue'],
        ];
    }

    /**
     * @dataProvider validateValues
     */
    public function testValidatesValuesPerRfc7230($value, $assertion)
    {
        $this->{$assertion}(HeaderSecurity::isValid($value));
    }

    public function assertValues()
    {
        return [
            ["This is a\n test"],
            ["This is a\r test"],
            ["This is a\n\r test"],
            ["This is a \r\ntest"],
            ["This is a \r\n\n test"],
            ["This is a\n\n test"],
            ["This is a\r\r test"],
            ["This is a \r\r\n test"],
            ["This is a \r\n\r\ntest"],
            ["This is a \r\n\n\r\n test"]
        ];
    }

    /**
     * @dataProvider assertValues
     */
    public function testAssertValidRaisesExceptionForInvalidValue($value)
    {
        $this->setExpectedException('InvalidArgumentException');
        HeaderSecurity::assertValid($value);
    }

    public function testAssertValid()
    {
        HeaderSecurity::assertValid("This is a \x7E test");
    }

    public function assertNames()
    {
        return [
            ['::'],
            ['à']
        ];
    }

    /**
     * @dataProvider assertNames
     */
    public function testAssertValidNameRaisesExceptionForInvalidValue($value)
    {
        $this->setExpectedException('InvalidArgumentException');

        HeaderSecurity::assertValidName($value);
    }

    public function testAssertValidName()
    {
        HeaderSecurity::assertValidName('Server');
    }
}
