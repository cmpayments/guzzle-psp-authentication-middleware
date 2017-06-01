<?php

namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

use Psr\Http\Message\RequestInterface;

/**
 * Class ConnectionMiddleware
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
class AuthenticationMiddleware
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var NonceGeneratorInterface
     */
    private $nonceGenerator;

    /**
     * @var TimestampGeneratorInterface
     */
    private $timestampGenerator;

    /**
     * AuthenticationMiddleware constructor.
     *
     * @param string $key    OAuth-Consumer-Key
     * @param string $secret OAuth-Consumer-Secret
     */
    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;

        $this->nonceGenerator = new RandomNonceGenerator();
        $this->timestampGenerator = new RandomTimestampGenerator();
    }

    /**
     * Overwrite the nonceGenerator-class. Used for the unit-test
     *
     * @param NonceGeneratorInterface $nonceGenerator
     */
    public function setNonceGenerator($nonceGenerator)
    {
        $this->nonceGenerator = $nonceGenerator;
    }

    /**
     * Overwrite the timestampGenerator-class. Used for the unit-test
     *
     * @param TimestampGeneratorInterface $timestampGenerator
     */
    public function setTimestampGenerator($timestampGenerator)
    {
        $this->timestampGenerator = $timestampGenerator;
    }


    /**
     * This function is called by Guzzle in order to initiate the middleware.
     *
     * @param callable $handler Representation of an outgoing, client-side request.
     *
     * @return \Closure
     */
    public function __invoke($handler)
    {
        return function ($request, array $options) use ($handler) {
            $request = $this->onBefore($request);

            return $handler($request, $options);
        };
    }

    /**
     * Before executing the request, add the extra headers
     *
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    private function onBefore(RequestInterface $request)
    {
        $headers = $this->createHeaders($request);
        foreach ($headers as $headerKey => $headerValue) {
            $request = $request->withHeader($headerKey, $headerValue);
        }

        return $request;
    }

    /**
     * Calculate/determine the required headers for the request.
     *
     * @param RequestInterface $request
     *
     * @return string[] The headers for the request
     */
    private function createHeaders(RequestInterface $request)
    {
        $nonce = $this->nonceGenerator->generate();
        $timestamp = $this->timestampGenerator->generate();

        /** @var array $data */
        $data = $this->createDataElement($request, $nonce, $timestamp);

        $payload = $request->getMethod() .
            '&' . rawurlencode((string)$request->getUri()) .
            '&' . implode(rawurlencode('&'), $data);

        $signkey = rawurlencode($this->key) . '&' . rawurlencode($this->secret);
        $hash = rawurlencode(base64_encode(hash_hmac('sha256', $payload, $signkey)));

        /** @var array $oauth_header */
        $oauth_header = $this->createOAuthHeaders($nonce, $timestamp, $hash);

        $header = [];
        $header['Content-type'] = 'application/json';
        $header['Authorization'] = 'OAuth ' . implode(', ', $oauth_header);

        return $header;
    }

    /**
     * Create the data array for the payload
     *
     * @param RequestInterface $request
     * @param string           $nonce The none which is generated
     * @param int              $timestamp The timestamp which is generated
     *
     * @return array The data-array
     */
    private function createDataElement(RequestInterface $request, string $nonce, int $timestamp)
    {
        $data = [];
        if ($request->getMethod() === 'POST') {
            $data[] = rawurlencode($request->getBody()->getContents());
        }
        $data[] = rawurlencode('oauth_consumer_key=' . $this->key);
        $data[] = rawurlencode('oauth_nonce=' . $nonce);
        $data[] = rawurlencode('oauth_signature_method=HMAC-SHA256');
        $data[] = rawurlencode('oauth_timestamp=' . $timestamp);
        $data[] = rawurlencode('oauth_version=1.0');

        return $data;
    }

    /**
     * Create the oAuth headers
     *
     * @param string $nonce The generated nonce
     * @param int    $timestamp The generated timestamp
     * @param string $hash The created hash
     *
     * @return array The headers for the request
     */
    private function createOAuthHeaders(string $nonce, int $timestamp, string $hash)
    {
        $oauth_header = [];
        $oauth_header[] = 'oauth_consumer_key="' . $this->key . '"';
        $oauth_header[] = 'oauth_nonce="' . $nonce . '"';
        $oauth_header[] = 'oauth_signature="' . $hash . '"';
        $oauth_header[] = 'oauth_signature_method="HMAC-SHA256"';
        $oauth_header[] = 'oauth_timestamp="' . $timestamp . '"';
        $oauth_header[] = 'oauth_version="1.0"';
        return $oauth_header;
    }
}
