<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\CertificateAuthority;

use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Ssl\KeyPair;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Create Acme Certificate Authority clients.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ClientFactory
{
    use LoggerAwareTrait;

    /** @var CertificateAuthorityConfigurationInterface */
    private $certificateAuthority;

    /**
     * @param CertificateAuthorityConfigurationInterface $certificateAuthority
     */
    public function __construct(CertificateAuthorityConfigurationInterface $certificateAuthority)
    {
        $this->certificateAuthority = $certificateAuthority;

        $this->logger = new NullLogger();
    }

    /**
     * Create a new instance of AcmeClient for the given account KeyPair.
     *
     * @param KeyPair $keyPair
     *
     * @return AcmeClient
     */
    public function createAcmeClient(KeyPair $keyPair)
    {
        return new AcmeClient(
            $this->certificateAuthority->getBaseUri(),
            $this->certificateAuthority->getAgreement(),
            $keyPair,
            $this->logger
        );
    }
}
