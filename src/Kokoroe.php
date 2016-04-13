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
namespace Kokoroe;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\NullLogger;
use Kokoroe\Http\Signature\SignatureAwareTrait;
use Kokoroe\Http\Signature\DefaultSignature;
use Kokoroe\Http\Signature\SignatureInterface;

/**
 * Class Kokoroe
 *
 * @package Kokoroe
 */
class Kokoroe implements LoggerAwareInterface
{
    /**
     * @const string Kokoroe SDK version
     */
    const VERSION = '1.0.0-alpha.1';

    /**
     * @const string Production API URL.
     */
    const BASE_API_URL = 'https://api.kokoroe.co';

    /**
     * @const string Default API version for requests.
     */
    const DEFAULT_API_VERSION = 'v1.0';

    /**
     * @const string Default locale
     */
    const DEFAULT_LOCALE = 'en';

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $defaultApiUrl;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $userIp;

    /**
     * @var string
     */
    protected $tracker;

    /**
     * @var string
     */
    protected $defaultApiVersion;

    /**
     * @var string
     */
    protected $defaultAccessToken;

    /**
     * @var string
     */
    protected $clientIdPattern = '/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/';

    /**
     * @var Http\Client
     */
    protected $http;

    /**
     * @var bool
     */
    protected $sslVerify;

    use LoggerAwareTrait;
    use SignatureAwareTrait;

    /**
     *
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        $this->setLogger(new NullLogger());
    }

    /**
     * Sets options
     *
     * @param array $options
     * @return Kokoroe
     * @throws Exception
     */
    public function setOptions(array $options)
    {
        if (isset($options['client_id'])) {
            $this->setClientId($options['client_id']);
        }

        if (isset($options['client_secret'])) {
            $this->setClientSecret($options['client_secret']);
        }

        if (isset($options['default_access_token'])) {
            $this->setDefaultAccessToken($options['default_access_token']);
        }

        if (isset($options['default_api_version'])) {
            $this->setDefaultApiVersion($options['default_api_version']);
        } else {
            $this->setDefaultApiVersion(self::DEFAULT_API_VERSION);
        }

        if (isset($options['default_api_url'])) {
            $this->setDefaultApiUrl($options['default_api_url']);
        } else {
            $this->setDefaultApiUrl(self::BASE_API_URL);
        }

        if (isset($options['locale'])) {
            $this->setLocale($options['locale']);
        } else {
            $this->setLocale(self::DEFAULT_LOCALE);
        }

        if (isset($options['ssl_verify'])) {
            $this->setSslVerify($options['ssl_verify']);
        } else {
            $this->setSslVerify(true);
        }

        if (isset($options['user_ip'])) {
            $this->setUserIp($options['user_ip']);
        }

        if (isset($options['tracker'])) {
            $this->setTracker($options['tracker']);
        }

        if (isset($options['signature'])) {
            if (is_bool($options['signature'])) {
                $this->setSignature(new DefaultSignature());
            } else if ($options['signature'] instanceof SignatureInterface) {
                $this->setSignature($options['signature']);
            }
        }

        return $this;
    }

    /**
     * Sets the client id
     *
     * @param string $clientId The client_id
     * @return Kokoroe
     * @throws Exception
     */
    public function setClientId($clientId)
    {
        if (preg_match($this->clientIdPattern, $clientId) === 0) {
            throw new Exception(sprintf('The client id "%s" is not valid.', $clientId));
        }

        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get the client id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Sets the client secret
     *
     * @param string $clientSecret The client_secret
     * @return Kokoroe
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get the client secret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @param string $accessToken The access token
     * @return Kokoroe
     */
    public function setDefaultAccessToken($accessToken)
    {
        $this->defaultAccessToken = $accessToken;

        return $this;
    }

    /**
     * Get the default access token
     *
     * @return string
     */
    public function getDefaultAccessToken()
    {
        return $this->defaultAccessToken;
    }

    /**
     * Set default api url
     *
     * @param string $url
     * @return Kokoroe
     */
    public function setDefaultApiUrl($url)
    {
        $this->defaultApiUrl = $url;

        return $this;
    }

    /**
     * Get default api url
     *
     * @return string
     */
    public function getDefaultApiUrl()
    {
        return $this->defaultApiUrl;
    }

    /**
     * Set default api version
     *
     * @param string $version
     * @return Kokoroe
     */
    public function setDefaultApiVersion($version)
    {
        $this->defaultApiVersion = $version;

        return $this;
    }

    /**
     * Get the default api version
     *
     * @return string
     */
    public function getDefaultApiVersion()
    {
        return $this->defaultApiVersion;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return Kokoroe
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set user ip
     *
     * @param string $userIp
     * @return Kokoroe
     */
    public function setUserIp($userIp)
    {
        $this->userIp = $userIp;

        return $this;
    }

    /**
     * Get user ip
     *
     * @return string
     */
    public function getUserIp()
    {
        return $this->userIp;
    }

     /**
     * Set user tracker
     *
     * @param string $tracker
     * @return Kokoroe
     */
    public function setTracker($tracker)
    {
        $this->tracker = $tracker;

        return $this;
    }

    /**
     * Get user tracker
     *
     * @return string
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * Set ssl verification
     *
     * @param bool $verify
     * @return Kokoroe
     */
    public function setSslVerify($verify)
    {
        $this->sslVerify = (bool) $verify;

        return $this;
    }

    /**
     * Get ssl verification
     *
     * @return bool
     */
    public function getSslVerify()
    {
        return (bool) $this->sslVerify;
    }

    /**
     * Returns the base Api URL.
     *
     * @return string
     */
    public function getBaseApiUrl()
    {
        return $this->defaultApiUrl . '/' . $this->defaultApiVersion;
    }

    /**
     * Get authorization header
     *
     * @param  string|null $accessToken
     * @return string
     */
    protected function getAuthorizationHeader($accessToken = null)
    {
        if (empty($accessToken)) {
            $accessToken = $this->defaultAccessToken;
        }

        if (empty($accessToken)) {
            return sprintf('Basic %s', base64_encode($this->clientId . ':'));
        } else {
            return sprintf('Bearer %s', $accessToken);
        }
    }

    /**
     * Set http client
     *
     * @param Http\Client $client
     * @return Kokoroe
     */
    public function setHttpClient(Http\Client $client)
    {
        $this->http = $client;
        $this->http->setLogger($this->logger);
        $this->http->getAdapter()->setSslVerify($this->sslVerify);

        if ($this->hasSignature()) {
            $this->getSignature()->setKey($this->clientSecret);

            $this->http->setSignature($this->getSignature());
        }

        return $this;
    }

    /**
     * Get http client
     *
     * @return Http\Client
     */
    public function getHttpClient()
    {
        if (empty($this->http)) {
            $this->setHttpClient(new Http\Client);
        }

        return $this->http;
    }

    /**
     * Check if client settings is correct
     *
     * @return void
     * @throws Exception
     */
    protected function checkClientSettings()
    {
        if (empty($this->clientId)) {
            throw new Exception('Required "client_id" key not supplied in options');
        }

        if (empty($this->clientSecret)) {
            throw new Exception('Required "client_secret" key not supplied in options');
        }
    }

    /**
     * Parse endpoint
     *
     * @param  string $endpoint The endpoint with query string
     * @return array
     */
    protected function parseEndpoint($endpoint)
    {
        $params = [];

        if (strpos($endpoint, '?') !== false) {
            list($endpoint, $query) = explode('?', $endpoint);
            parse_str($query, $params);
        }

        return [
            'endpoint' => $this->getBaseApiUrl() . '/' . trim($endpoint, '/'),
            'params' => $params
        ];
    }

    /**
     * Get http headers
     *
     * @param  string $accessToken
     * @return array
     */
    protected function getHeaders($accessToken = null)
    {
        $headers = [];

        $headers['Authorization']   = $this->getAuthorizationHeader($accessToken);
        $headers['Accept-Language'] = $this->locale;

        if (!empty($this->userIp)) {
            $headers['X-Forwarded-For'] = $this->userIp;
        }

        if (!empty($this->tracker)) {
            $headers['X-Kokoroe-Tracker'] = $this->tracker;
        }

        return $headers;
    }

    /**
     * Send get request
     *
     * @param  string $endpoint
     * @param  string $accessToken
     * @return Http\Response
     * @throws UnexpectedValueException
     */
    public function get($endpoint, $accessToken = null)
    {
        $this->checkClientSettings();

        $data = $this->parseEndpoint($endpoint);

        return $this->getHttpClient()->get(
            $data['endpoint'],
            $data['params'],
            $this->getHeaders($accessToken)
        );
    }

    /**
     * Send post request
     *
     * @param  string $endpoint
     * @param  mixed  $body
     * @param  string $accessToken
     * @return Http\Response
     * @throws UnexpectedValueException
     */
    public function post($endpoint, $body, $accessToken = null)
    {
        $this->checkClientSettings();

        $data = $this->parseEndpoint($endpoint);

        return $this->getHttpClient()->post(
            $data['endpoint'],
            $data['params'],
            $body,
            $this->getHeaders($accessToken)
        );
    }

    /**
     * Send put request
     *
     * @param  string $endpoint
     * @param  mixed  $body
     * @param  string $accessToken
     * @return Http\Response
     * @throws UnexpectedValueException
     */
    public function put($endpoint, $body, $accessToken = null)
    {
        $this->checkClientSettings();

        $data = $this->parseEndpoint($endpoint);

        return $this->getHttpClient()->put(
            $data['endpoint'],
            $data['params'],
            $body,
            $this->getHeaders($accessToken)
        );
    }

    /**
     * Send delete request
     *
     * @param  string $endpoint
     * @param  string $accessToken
     * @return Http\Response
     * @throws UnexpectedValueException
     */
    public function delete($endpoint, $accessToken = null)
    {
        $this->checkClientSettings();

        $data = $this->parseEndpoint($endpoint);

        return $this->getHttpClient()->delete(
            $data['endpoint'],
            $data['params'],
            $this->getHeaders($accessToken)
        );
    }
}
