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
     * @return integer
     */
    public function generate();
}
