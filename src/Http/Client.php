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
namespace Kokoroe\Http;

use Kokoroe\Http\Client\Adapter\AdapterInterface;
use Kokoroe\Http\Signature\SignatureInterface;
use Kokoroe\Http\Signature\SignatureAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;

/**
 * Http Client Interface
 *
 * @package Kokoroe
 */
class Client implements LoggerAwareInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var integer
     */
    protected $timeout = 60;

    use LoggerAwareTrait;
    use SignatureAwareTrait;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setLogger(new NullLogger());
    }

    /**
     * Set http client adapter
     *
     * @param AdapterInterface $adapter
     * @return Client
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        if (empty($this->adapter)) {
            $this->adapter = new Client\Adapter\Curl();
        }

        return $this->adapter;
    }

    /**
     * Sign url
     *
     * @param  string $url
     * @param  array  $params
     * @return string
     */
    protected function signUrl($url, array $params = [])
    {
        if (!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        return $this->signature->sign($url);
    }

    /**
     * Send request.
     *
     * @param  string $method
     * @param  string $url
     * @param  array  $params
     * @param  string $body
     * @param  array  $headers
     * @return Response
     * @throws UnexpectedValueException
     */
    public function send($method, $url, array $params = [], $body = null, array $headers = [])
    {
        if ($this->hasSignature()) {
            $params['sign'] = $this->signUrl($url, $params);
        }

        if (!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        $this->logger->info(sprintf('Send %s request on %s', $method, $url), [
            'headers'   => $headers,
            'body'      => $body
        ]);

        return $this->getAdapter()->send($method, $url, $body, $headers, $this->timeout);
    }

    /**
     * Send get request
     *
     * @param  string $url
     * @param  array  $params
     * @param  array  $headers
     * @return Response
     * @throws UnexpectedValueException
     */
    public function get($url, array $params, array $headers = [])
    {
        return $this->send('GET', $url, $params, null, $headers);
    }

    /**
     * Send post request
     *
     * @param  string $url
     * @param  array  $params
     * @param  string $body
     * @param  array  $headers
     * @return Response
     * @throws UnexpectedValueException
     */
    public function post($url, array $params, $body = null, array $headers = [])
    {
        return $this->send('POST', $url, $params, $body, $headers);
    }

    /**
     * Send put request
     *
     * @param  string $url
     * @param  array  $params
     * @param  string $body
     * @param  array  $headers
     * @return Response
     * @throws UnexpectedValueException
     */
    public function put($url, array $params, $body = null, array $headers = [])
    {
        return $this->send('PUT', $url, $params, $body, $headers);
    }

    /**
     * Send delete request
     *
     * @param  string $url
     * @param  array  $params
     * @param  array  $headers
     * @return Response
     * @throws UnexpectedValueException
     */
    public function delete($url, array $params, array $headers = [])
    {
        return $this->send('DELETE', $url, $params, null, $headers);
    }
}
