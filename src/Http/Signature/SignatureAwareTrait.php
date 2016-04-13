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
trait SignatureAwareTrait
{
    /**
     * @var SignatureInterface
     */
    protected $signature;

    /**
     * Set signature
     *
     * @param SignatureInterface $signature
     * @return SignatureAwareTrait
     */
    public function setSignature(SignatureInterface $signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return SignatureInterface
     */
    public function getSignature()
    {
        if (empty($this->signature)) {
            $this->signature = new DefaultSignature();
        }

        return $this->signature;
    }

    /**
     * Check if signature is enabled
     *
     * @return boolean
     */
    public function hasSignature()
    {
        return (!empty($this->signature));
    }
}
