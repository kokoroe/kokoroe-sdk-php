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
namespace Kokoroe\Http\Client\Adapter;

/**
 * Http Client Adapter Interface
 *
 * @package Kokoroe
 */
interface AdapterInterface
{

    /**
     * Set ssl verification
     *
     * @param bool $verify
     * @return AdapterInterface
     */
    public function setSslVerify($verify);

    /**
     * Check if ssl verification
     *
     * @return bool
     */
    public function isSslVerify();

    /**
     * Sends a request to the server and returns the raw response.
     *
     * @param  string  $method  The request method.
     * @param  string  $url     The endpoint to send the request to.
     * @param  string  $body    The body of the request.
     * @param  array   $headers The request headers.
     * @param  integer $timeout The timeout in seconds for the request.
     * @return Psr\Http\Message\ResponseInterface
     */
    public function send($method, $url, $body, array $headers, $timeout);
}
