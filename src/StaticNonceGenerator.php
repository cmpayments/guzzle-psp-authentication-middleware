<?php

namespace CMPayments\GuzzlePSPAuthenticationMiddleware;

/**
 * Class StaticNonceGenerator
 *
 * @package CMPayments\GuzzlePSPAuthenticationMiddleware
 */
class StaticNonceGenerator implements NonceGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate()
    {
        return '31a20fedf919e19db9f438e9bd9610f9';
    }
}
