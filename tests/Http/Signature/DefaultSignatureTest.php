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

use Kokoroe\Http\Signature\DefaultSignature;

/**
 * Default Signature Test
 */
class DefaultSignatureTest extends \PHPUnit_Framework_TestCase
{
    protected $key = '32c8e4373e94c8ffa0a05c575d8cae75d1e98c723b877638b606ad53668a';

    public function testInterface()
    {
        $signature = new DefaultSignature();

        $this->assertInstanceOf('\Kokoroe\Http\Signature\SignatureInterface', $signature);
    }

    public function testSign()
    {
        $signature = new DefaultSignature();
        $signature->setKey($this->key);

        $sign1 = $signature->sign(
            'https://api.domain.com/v1/search?client_id=7f76ff8615d64e788ea5e9633def1625&term=hello&fields=type,translations{title,program}'
        );

        $sign2 = $signature->sign(
            'https://api.domain.com/v1/search?fields=type,translations{title,program}&client_id=7f76ff8615d64e788ea5e9633def1625&term=hello'
        );

        $this->assertEquals($sign1, $sign2);
    }
}
