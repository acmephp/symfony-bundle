<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate\Formatter;

use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;

/**
 * Format the full chain certificate to the certificate authority combined with the private key.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CombinedFormatter implements FormatterInterface
{
    /** @var CertificateAuthorityConfigurationInterface */
    private $certificateAuthorityConfiguration;

    /**
     * Formatter constructor.
     *
     * @param CertificateAuthorityConfigurationInterface $certificateAuthorityConfiguration
     */
    public function __construct(CertificateAuthorityConfigurationInterface $certificateAuthorityConfiguration)
    {
        $this->certificateAuthorityConfiguration = $certificateAuthorityConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'combined.pem';
    }

    /**
     * {@inheritdoc}
     */
    public function format(Certificate $certificate, KeyPair $domainKeyPair)
    {
        return $certificate->getPem().implode(
            '',
            $this->certificateAuthorityConfiguration->getCertificatesChain()
        ).$domainKeyPair->getPrivateKeyAsPEM();
    }
}
