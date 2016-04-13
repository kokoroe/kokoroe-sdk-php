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
 * Http Signature Default
 *
 * @package Kokoroe
 */
class DefaultSignature implements SignatureInterface
{
    /**
     * @var string
     */
    protected $key;

    /**
     * {@inheritdoc}
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sign($url)
    {
        $endpoint   = http_build_url($url, [], HTTP_URL_STRIP_QUERY | HTTP_URL_STRIP_FRAGMENT);
        $params     = [];
        $sig        = $endpoint;

        if (strpos($url, '?') !== false) {
            parse_str(parse_url($url, PHP_URL_QUERY), $params);
        }

        if (!empty($params)) {
            ksort($params);
            $sig .= sprintf('?%s', http_build_query($params));
        }

        return hash_hmac('sha256', $sig, $this->key, false);
    }
}
