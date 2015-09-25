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

/**
 * Class Kokoroe
 *
 * @package Kokoroe
 */
class Kokoroe
{
    /**
     * @const string Kokoroe SDK version
     */
    const VERSION = '1.0.0';

    /**
     * @const string Production API URL.
     */
    const BASE_API_URL = 'https://api.kokoroe.co';

    /**
     * @const string Default API version for requests.
     */
    const DEFAULT_API_VERSION = 'v1.0';

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
     *
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
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
            $this->http = new Http\Client;
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
     * Send get request
     *
     * @param  string $endpoint
     * @param  string $accessToken
     * @return array|null
     * @throws UnexpectedValueException
     */
    public function get($endpoint, $accessToken = null)
    {
        $this->checkClientSettings();

        $data = $this->parseEndpoint($endpoint);

        return $this->getHttpClient()->get(
            $data['endpoint'],
            $data['params'],
            [
                'Authorization' => $this->getAuthorizationHeader($accessToken)
            ]
        );
    }

    /**
     * Send post request
     *
     * @param  string $endpoint
     * @param  mixed  $body
     * @param  string $accessToken
     * @return array|null
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
            [
                'Authorization' => $this->getAuthorizationHeader($accessToken)
            ]
        );
    }

    /**
     * Send put request
     *
     * @param  string $endpoint
     * @param  mixed  $body
     * @param  string $accessToken
     * @return array|null
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
            [
                'Authorization' => $this->getAuthorizationHeader($accessToken)
            ]
        );
    }

    /**
     * Send delete request
     *
     * @param  string $endpoint
     * @param  string $accessToken
     * @return array|null
     * @throws UnexpectedValueException
     */
    public function delete($endpoint, $accessToken = null)
    {
        $this->checkClientSettings();

        $data = $this->parseEndpoint($endpoint);

        return $this->getHttpClient()->delete(
            $data['endpoint'],
            $data['params'],
            [
                'Authorization' => $this->getAuthorizationHeader($accessToken)
            ]
        );
    }
}
