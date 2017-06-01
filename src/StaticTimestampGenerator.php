<?php
namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

/**
 * Class StaticTimestampGenerator
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
class StaticTimestampGenerator implements TimestampGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate()
    {
        return 1496304084;
    }
}
