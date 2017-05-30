<?php
namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

/**
 * Class RandomNonceGenerator
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
class RandomNonceGenerator implements NonceGeneratorInterface
{
    /**
     * Generate a Nonce
     *
     * @return string
     */
    public function generate()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }
}
