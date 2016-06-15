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

use Kokoroe;
use Kokoroe\Http\Response;
use RuntimeException;

/**
 * Http Client Curl Adapter
 *
 * @package Kokoroe
 */
class Curl implements AdapterInterface
{
    /**
     * @var bool
     */
    protected $sslVerify;

    /**
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        if (!extension_loaded('curl')) {
            throw new RuntimeException('Missing ext/curl');
        }
        // @codeCoverageIgnoreEnd

        $this->sslVerify = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setSslVerify($verify)
    {
        $this->sslVerify = (bool) $verify;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSslVerify()
    {
        return $this->sslVerify;
    }

    /**
     * {@inheritdoc}
     */
    public function send($method, $url, $body, array $headers, $timeout)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, sprintf(
            'Kokoroe/SDK (version %s +https://github.com/kokoroe/kokoroe-sdk-php)',
            Kokoroe\Kokoroe::VERSION
        ));

        $isMultiPart = false;

        if (is_array($body)) {
            foreach ($body as $key => $file) {
                if (is_a($file, 'SplFileInfo')) {
                    $isMultiPart = true;
                    $body[$key] = curl_file_create($file->getRealPath(), mime_content_type($file->getRealPath()), $file->getFilename());
                }
            }
        }

        if (!empty($headers)) {
            array_walk($headers, function(&$value, $key) {
                $value = sprintf('%s: %s', $key, $value);
            });

            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($headers));
        }

        if ($this->sslVerify === false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $curlValue = true;

        $method = strtoupper($method);

        switch ($method) {
            case 'GET':
                $curlMethod = CURLOPT_HTTPGET;
                break;

            case 'HEAD':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = 'HEAD';
                break;

            case 'POST':
                if (!$isMultiPart &&
                    !is_string($body)) {
                    $body = http_build_query($body, '', '&');
                }

                $curlMethod = CURLOPT_POST;
                break;

            case 'PUT':
                if (!$isMultiPart &&
                    !is_string($body)) {
                    $body = http_build_query($body, '', '&');
                }

                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = 'PUT';
                break;

            case 'DELETE':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = 'DELETE';
                break;

            /*case 'PATCH':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = 'PATCH';
                break;

            case 'TRACE':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = 'TRACE';
                break;

            case 'OPTIONS':
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = 'OPTIONS';
                break;*/
        }

        // mark as HTTP request and set HTTP method
        curl_setopt($ch, $curlMethod, $curlValue);

        /**
         * Make sure POSTFIELDS is set after $curlMethod is set:
         * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
         */
        if ($method == 'POST' || $method == 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }/* elseif ($method == 'PATCH') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }*/

        unset($body);

        $response = curl_exec($ch);

        if ($response === false) {
            // @codeCoverageIgnoreStart
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException($error);
            // @codeCoverageIgnoreEnd
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        // Eliminate multiple HTTP responses.
        do {
            $parts  = preg_split('|(?:\r?\n){2}|m', $response, 2);
            $again  = false;

            // @codeCoverageIgnoreStart
            if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
                $response    = $parts[1];
                $again       = true;
            }
            // @codeCoverageIgnoreEnd
        } while ($again);

        // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
        // @codeCoverageIgnoreStart
        if (stripos($response, "HTTP/1.0 200 Connection established\r\n\r\n") !== false) {
            $response = str_ireplace("HTTP/1.0 200 Connection established\r\n\r\n", '', $response);
        }
        // @codeCoverageIgnoreEnd

        list($header, $body) = explode("\r\n\r\n", $response);

        unset($response);

        $headers = explode("\r\n", $header);
        unset($headers[0], $header);

        $response = new Response();
        $response->setStatusCode($info['http_code']);

        foreach ($headers as $value) {
            if (strpos($value, ': ') !== false) {
                list($key, $val) = explode(': ', $value);
                $response->headers->set($key, $val);
            }
        }

        unset($headers);

        if (!empty($body)) {
            $response->setContent($body);
        }

        return $response;
    }
}
