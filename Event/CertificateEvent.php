<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Event;

use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class for events thrown in the AcmePhp bundle related to certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateEvent extends Event
{
    /** @var DomainConfiguration */
    private $domainConfiguration;

    /** @var Certificate */
    private $certificate;

    /** @var KeyPair */
    private $domainKeyPair;

    /**
     * CertificateEvent constructor.
     *
     * @param DomainConfiguration $domainConfiguration
     * @param Certificate         $certificate
     * @param KeyPair             $domainKeyPair
     */
    public function __construct(DomainConfiguration $domainConfiguration, Certificate $certificate, KeyPair $domainKeyPair)
    {
        $this->domainConfiguration = $domainConfiguration;
        $this->certificate = $certificate;
        $this->domainKeyPair = $domainKeyPair;
    }

    /**
     * @return DomainConfiguration
     */
    public function getDomainConfiguration()
    {
        return $this->domainConfiguration;
    }

    /**
     * @return Certificate
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return KeyPair
     */
    public function getDomainKeyPair()
    {
        return $this->domainKeyPair;
    }
}
