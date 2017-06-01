<?php

namespace CMPayments\GuzzlePSPAuthenticationMiddleware\test;

use CMPayments\GuzzlePSPAuthenticationMiddleware\AuthenticationMiddleware;
use CMPayments\GuzzlePSPAuthenticationMiddleware\StaticNonceGenerator;
use CMPayments\GuzzlePSPAuthenticationMiddleware\StaticTimestampGenerator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

/**
 * Class AuthenticationTest
 *
 * @package GuzzlePSPAuthenticationMiddleware\test
 */
class AuthenticationTest extends \PHPUnit_Framework_TestCase
{
    /**
     *  Base uri
     */
    const BASEURI = 'http://api.cmpayments.com';

    /**
     * User guid
     */
    const KEY = 'test';

    /**
     * User secret
     */
    const SECRET = 'your-secret';

    /**
     * The uri part of the charge
     */
    const URI_CHARGE = 'charges/v1';

    /**
     * The uri part of retrieving the iDEAL issuers
     */
    const URI_IDEAL_ISSUER = 'issuers/v1/ideal';

    /**
     * The header-key which will includes the Authoriazation headers
     */
    const HEADER_AUTHORIZATION = 'Authorization';

    /**
     * Get-method
     */
    const METHOD_GET = 'GET';

    /**
     * Post-method
     */
    const METHOD_POST = 'POST';

    /**
     * @var array The history middleware stores the request in this array.
     */
    private $container = [];

    /**
     * @param bool $static Does it have to use the static generators or not.
     *
     * @return AuthenticationMiddleware The actual AuthenticationMiddleware
     */
    public function createAuth($static = false)
    {
        $auth = new AuthenticationMiddleware(self::KEY, self::SECRET);
        if ($static) {
            $auth->setNonceGenerator(new StaticNonceGenerator());
            $auth->setTimestampGenerator(new StaticTimestampGenerator());
        }

        return $auth;
    }

    /**
     * Test a GET-request
     */
    public function testRequestHeadersWithGet()
    {
        $client = $this->createNewClient(true);
        $client->request(self::METHOD_GET, self::URI_IDEAL_ISSUER, []);

        /**
         * @var \GuzzleHttp\Psr7\Request $request
         */
        $request = $this->container[0]['request'];
        $this->assertArrayHasKey(self::HEADER_AUTHORIZATION, $request->getHeaders());
        $authHeaderParts = explode(', ', $request->getHeader(self::HEADER_AUTHORIZATION)[0]);
        $staticNonce = (new StaticNonceGenerator())->generate();
        $staticTimestamp = (new StaticTimestampGenerator())->generate();

        $this->assertEquals('OAuth oauth_consumer_key="' . self::KEY . '"', $authHeaderParts[0]);
        $this->assertEquals('oauth_nonce="' . $staticNonce . '"', $authHeaderParts[1]);
        // @codingStandardsIgnoreStart
        $this->assertEquals('oauth_signature="YjM5MWI5MTNjZTg1ZDRkMzFhNDJhMzk0NjFkYzYzNmJhNDk4ZTM3YTYyNDhiZWU3NTNhYjgxZTJiMTFhZDYzZg%3D%3D"', $authHeaderParts[2]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals('oauth_signature_method="HMAC-SHA256"', $authHeaderParts[3]);
        $this->assertEquals('oauth_timestamp="' . $staticTimestamp . '"', $authHeaderParts[4]);
        $this->assertEquals('oauth_version="1.0"', $authHeaderParts[5]);
    }

    /**
     * Test a POST-request
     */
    public function testRequestHeadersWithPOST()
    {
        $body = [
            'amount'   => 15.95,
            'currency' => 'EUR',
            'payments' => [
                [
                    'amount'          => 15.95,
                    'currency'        => 'EUR',
                    'payment_method'  => 'iDEAL',
                    'payment_details' => [
                        'issuer_id'     => 'RABONL2U',
                        'purchase_id'   => 'unqiue' . (new StaticTimestampGenerator())->generate(),
                        'description'   => 'Transaction description',
                        'success_url'   => 'http://www.yourdomain.com/ideal/success',
                        'failed_url'    => 'http://www.yourdomain.com/ideal/failed',
                        'cancelled_url' => 'http://www.yourdomain.com/ideal/cancelled',
                        'expired_url'   => 'http://www.yourdomain.com/ideal/expired'
                    ]
                ],

            ],
        ];

        $client = $this->createNewClient(true);
        $client->request(self::METHOD_POST, self::URI_IDEAL_ISSUER, ['json' => $body]);

        /**
         * @var \GuzzleHttp\Psr7\Request $request
         */
        $request = $this->container[0]['request'];
        $this->assertArrayHasKey(self::HEADER_AUTHORIZATION, $request->getHeaders());
        $authHeaderParts = explode(', ', $request->getHeader(self::HEADER_AUTHORIZATION)[0]);
        $staticNonce = (new StaticNonceGenerator())->generate();
        $staticTimestamp = (new StaticTimestampGenerator())->generate();

        $this->assertEquals('OAuth oauth_consumer_key="' . self::KEY . '"', $authHeaderParts[0]);
        $this->assertEquals('oauth_nonce="' . $staticNonce . '"', $authHeaderParts[1]);
        // @codingStandardsIgnoreStart
        $this->assertEquals('oauth_signature="NzI3MmU3ZTg3ZWYyNDBjMDA1ODkyMGIxOTMyYThiMzcxOGQ3YWY3ZjY3NDAyN2JiMzBmNTc0MjY3YmE0MWFlNQ%3D%3D"', $authHeaderParts[2]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals('oauth_signature_method="HMAC-SHA256"', $authHeaderParts[3]);
        $this->assertEquals('oauth_timestamp="' . $staticTimestamp . '"', $authHeaderParts[4]);
        $this->assertEquals('oauth_version="1.0"', $authHeaderParts[5]);
    }

    /**
     * As for a GET and a POST, the initiation of the request is the same.
     *
     * @param bool $static Does it have to use the static generators or not.
     *
     * @return Client A Guzzle-client
     */
    private function createNewClient($static = false)
    {
        /*
         * Mock the response, this because we don't have to execute
         * a real request. When we mock-the response guzzle will execute the
         * authentication-middleware-part of the request.
         */
        $mock = new MockHandler([
            new \GuzzleHttp\Psr7\Response(200, ['X-Foo' => 'Bar'])
        ]);

        $stack = HandlerStack::create($mock);
        $AuthenticationMiddleware = $this->createAuth($static);

        $this->container = [];
        $historyMiddleware = Middleware::history($this->container);

        $stack->push($AuthenticationMiddleware);
        $stack->push($historyMiddleware);

        return new Client(['base_uri' => self::BASEURI, 'handler' => $stack]);
    }
}
