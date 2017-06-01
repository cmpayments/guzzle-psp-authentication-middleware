<?php

namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

/**
 * Interface TimestampInterface
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
interface TimestampGeneratorInterface
{
    /**
     * Generate a timestamp
     *
     * @return integer A timestamp
     */
    public function generate();
}
