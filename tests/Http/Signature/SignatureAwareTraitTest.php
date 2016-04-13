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
namespace Kokoroe\Tests\Http\Signature;

use Kokoroe\Http\Signature\SignatureAwareTrait;

/**
 * Signature Aware Trait Test
 */
class SignatureAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testTrait()
    {
        $signatureMock = $this->getMock('Kokoroe\Http\Signature\SignatureInterface');

        $trait = $this->getMockForTrait('Kokoroe\Http\Signature\SignatureAwareTrait');

        $this->assertFalse($trait->hasSignature());

        $this->assertInstanceOf('Kokoroe\Http\Signature\SignatureInterface', $trait->getSignature());

        $this->assertTrue($trait->hasSignature());

        $trait->setSignature($signatureMock);

        $this->assertEquals($signatureMock, $trait->getSignature());
    }
}
