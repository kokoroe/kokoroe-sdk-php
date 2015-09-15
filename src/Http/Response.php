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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * Http Response
 */
class Response implements ResponseInterface
{
    /**
     * Map of standard HTTP status code/reason phrases
     *
     * @var array
     */
    private $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];

    /**
     * @var string
     */
    private $reasonPhrase;

    /**
     * @var integer
     */
    private $statusCode = 200;

    /**
     * @var string
     */
    private $protocol = '1.1';

    /**
     * @var StreamInterface
     */
    private $stream;

    /**
     * List of all registered headers, as key => array of values.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Map of normalized header name to original name used to register header.
     *
     * @var array
     */
    private $headerNames = [];

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        if (empty($this->reasonPhrase) && isset($this->phrases[$this->statusCode])) {
            $this->reasonPhrase = $this->phrases[$this->statusCode];
        }

        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->validateStatus($code);
        $new = clone $this;
        $new->statusCode   = (int) $code;
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * Validate a status code.
     *
     * @param integer|string $code
     * @throws InvalidArgumentException on an invalid status code.
     */
    private function validateStatus($code)
    {
        if (!is_numeric($code) || is_float($code) || $code < 100 || $code >= 600) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                (is_scalar($code) ? $code : gettype($code))
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return array_key_exists(strtolower($name), $this->headerNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        $header = $this->headerNames[strtolower($name)];
        $value  = $this->headers[$header];
        $value  = is_array($value) ? $value : [$value];

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);

        if (empty($value)) {
            return '';
        }

        return implode(',', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (! is_array($value) || ! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        HeaderSecurity::assertValidName($name);
        self::assertValidHeaderValue($value);

        $normalized = strtolower($name);

        $new = clone $this;
        $new->headerNames[$normalized] = $name;
        $new->headers[$name]           = $value;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value)
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (!is_array($value) || ! $this->arrayContainsOnlyStrings($value)) {
            throw new InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }

        HeaderSecurity::assertValidName($name);
        self::assertValidHeaderValue($value);

        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $normalized = strtolower($name);
        $name       = $this->headerNames[$normalized];
        $new        = clone $this;

        $new->headers[$name] = array_merge($this->headers[$name], $value);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return clone $this;
        }

        $normalized = strtolower($name);
        $original   = $this->headerNames[$normalized];
        $new        = clone $this;

        unset($new->headers[$original], $new->headerNames[$normalized]);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->stream;
    }

    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->stream = $body;

        return $new;
    }

    /**
     * Test if a value is a string
     *
     * Used with array_reduce.
     *
     * @param bool $carry
     * @param mixed $item
     * @return bool
     */
    private static function filterStringValue($carry, $item)
    {
        if (!is_string($item)) {
            return false;
        }

        return $carry;
    }

    /**
     * Test that an array contains only strings
     *
     * @param array $array
     * @return bool
     */
    private function arrayContainsOnlyStrings(array $array)
    {
        return array_reduce($array, [__CLASS__, 'filterStringValue'], true);
    }

    /**
     * Assert that the provided header values are valid.
     *
     * @see http://tools.ietf.org/html/rfc7230#section-3.2
     * @param string[] $values
     * @throws InvalidArgumentException
     */
    private static function assertValidHeaderValue(array $values)
    {
        array_walk($values, __NAMESPACE__ . '\HeaderSecurity::assertValid');
    }
}
