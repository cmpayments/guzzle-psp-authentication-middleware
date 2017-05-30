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
     * @inheritdoc
     */
    public function generate();
}
