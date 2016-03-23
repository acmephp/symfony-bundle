<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate;

use AcmePhp\Core\Ssl\Certificate;

/**
 * Entity representing a certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateMetadata
{
    /** @var string */
    private $subject;
    /** @var string */
    private $serialNumber;
    /** @var string */
    private $issuer;
    /** @var bool */
    private $selfSigned;
    /** @var array */
    private $subjectAlternativeNames;

    /**
     * @param string $subject
     * @param string $serialNumber
     * @param string $issuer
     * @param bool   $selfSigned
     * @param array  $subjectAlternativeNames
     */
    public function __construct(
        $subject,
        $issuer = null,
        $selfSigned = true,
        $serialNumber = null,
        array $subjectAlternativeNames = []
    ) {
        $this->subject = $subject;
        $this->serialNumber = $serialNumber;
        $this->issuer = $issuer;
        $this->selfSigned = $selfSigned;
        $this->subjectAlternativeNames = $subjectAlternativeNames;
    }

    public function merge(CertificateMetadata $other)
    {
        if (null !== $otherSubject = $other->getSubject()) {
            $this->subject = $otherSubject;
        }
        if (null !== $otherSerialNumber = $other->getSerialNumber()) {
            $this->serialNumber = $otherSerialNumber;
        }
        if (null !== $otherIssuer = $other->getIssuer()) {
            $this->issuer = $otherIssuer;
        }
        if (true !== $otherSelfSigned = $other->isSelfSigned()) {
            $this->selfSigned = $otherSelfSigned;
        }
        $this->subjectAlternativeNames += $other->getSubjectAlternativeNames();
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @return string
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return bool
     */
    public function isSelfSigned()
    {
        return $this->selfSigned;
    }

    /**
     * @return array
     */
    public function getSubjectAlternativeNames()
    {
        return $this->subjectAlternativeNames;
    }
}
