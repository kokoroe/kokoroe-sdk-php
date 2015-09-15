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
use UnexpectedValueException;

/**
 * Http Client Interface
 *
 * @package Kokoroe
 */
class Client
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var integer
     */
    protected $timeout = 60;

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
     * Send request.
     *
     * @param  string $method
     * @param  string $url
     * @param  array  $params
     * @param  string $body
     * @param  array  $headers
     * @return array|null
     * @throws UnexpectedValueException
     */
    public function send($method, $url, array $params = [], $body = null, array $headers = [])
    {
        if (!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        $response = $this->getAdapter()->send($method, $url, $body, $headers, $this->timeout);

        if (empty($response)) {
            return null;
        }

        $json = json_decode((string) $response->getBody(), true);

        $lastError = JSON_ERROR_NONE;

        if (JSON_ERROR_NONE !== $lastError = json_last_error()) {
            if (function_exists('json_last_error_msg')) {
                $message = json_last_error_msg();
                // @codeCoverageIgnoreStart
            } else {
                switch ($lastError) {
                    case JSON_ERROR_DEPTH:
                        $message = 'Maximum stack depth exceeded';
                    case JSON_ERROR_STATE_MISMATCH:
                        $message = 'Underflow or the modes mismatch';
                    case JSON_ERROR_CTRL_CHAR:
                        $message = 'Unexpected control character found';
                    case JSON_ERROR_SYNTAX:
                        $message = 'Syntax error, malformed JSON';
                    case JSON_ERROR_UTF8:
                        $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                    default:
                        $message = 'Unknown error';
                }
            }
            // @codeCoverageIgnoreEnd

            throw new UnexpectedValueException($message);
        }

        return $json;
    }

    /**
     * Send get request
     *
     * @param  string $url
     * @param  array  $params
     * @param  array  $headers
     * @return array|null
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
     * @return array|null
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
     * @return array|null
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
     * @return array|null
     * @throws UnexpectedValueException
     */
    public function delete($url, array $params, array $headers = [])
    {
        return $this->send('DELETE', $url, $params, null, $headers);
    }
}
