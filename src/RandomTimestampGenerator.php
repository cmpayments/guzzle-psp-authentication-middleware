<?php
namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

/**
 * Class RandomTimestampGenerator
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
class RandomTimestampGenerator implements TimestampGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate()
    {
        return time();
    }
}
