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
     * @param string $key
     * @param string $secret
     */
    public function __construct(string $key, string $secret)
    {
        $this->key = $key;
        $this->secret = $secret;

        $this->nonceGenerator = new RandomNonceGenerator();
        $this->timestampGenerator = new RandomTimestampGenerator();
    }

    /**
     * @param NonceGeneratorInterface $nonceGenerator
     */
    public function setNonceGenerator($nonceGenerator)
    {
        $this->nonceGenerator = $nonceGenerator;
    }

    /**
     * @param TimestampGeneratorInterface $timestampGenerator
     */
    public function setTimestampGenerator($timestampGenerator)
    {
        $this->timestampGenerator = $timestampGenerator;
    }


    /**
     * @param callable $handler
     *
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function ($request, array $options) use ($handler) {
            $request = $this->onBefore($request);

            return $handler($request, $options);
        };
    }

    /**
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
     * @param RequestInterface $request
     *
     * @return array
     */
    private function createHeaders(RequestInterface $request)
    {
        $data = [];
        $nonce = $this->nonceGenerator->generate();
        $timestamp = $this->timestampGenerator->generate();
        if ($request->getMethod() === 'POST') {
            $data[] = rawurlencode($request->getBody()->getContents());
        }
        $data[] = rawurlencode('oauth_consumer_key=' . $this->key);
        $data[] = rawurlencode('oauth_nonce=' . $nonce);
        $data[] = rawurlencode('oauth_signature_method=HMAC-SHA256');
        $data[] = rawurlencode('oauth_timestamp=' . $timestamp);
        $data[] = rawurlencode('oauth_version=1.0');

        $payload = $request->getMethod() .
            '&' . rawurlencode((string)$request->getUri()) .
            '&' . implode(rawurlencode('&'), $data);

        $signkey = rawurlencode($this->key) . '&' . rawurlencode($this->secret);
        $hash = rawurlencode(base64_encode(hash_hmac('sha256', $payload, $signkey)));

        $oauth_header = [];
        $oauth_header[] = 'oauth_consumer_key="' . $this->key . '"';
        $oauth_header[] = 'oauth_nonce="' . $nonce . '"';
        $oauth_header[] = 'oauth_signature="' . $hash . '"';
        $oauth_header[] = 'oauth_signature_method="HMAC-SHA256"';
        $oauth_header[] = 'oauth_timestamp="' . $timestamp . '"';
        $oauth_header[] = 'oauth_version="1.0"';

        $header = [];
        $header['Content-type'] = 'application/json';
        $header['Authorization'] = 'OAuth ' . implode(', ', $oauth_header);

        return $header;
    }
}
