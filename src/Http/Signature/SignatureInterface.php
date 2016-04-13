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
namespace Kokoroe\Http\Signature;

/**
 * Http Signature Interface
 *
 * @package Kokoroe
 */
interface SignatureInterface
{
    /**
     * Set key
     *
     * @param string $key
     * @return SignatureInterface
     */
    public function setKey($key);

    /**
     * Sign url
     *
     * @param  string $url
     * @return string
     */
    public function sign($url);
}
