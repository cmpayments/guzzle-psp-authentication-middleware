<?php

namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

/**
 * Interface NonceGeneratorInterface
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
interface NonceGeneratorInterface
{
    /**
     * Generate a Nonce
     *
     * @return string The nonce
     */
    public function generate();
}
