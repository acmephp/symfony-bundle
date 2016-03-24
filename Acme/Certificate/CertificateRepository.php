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

use AcmePhp\Bundle\Acme\Certificate\Formatter\CertificateFormatter;
use AcmePhp\Bundle\Acme\Certificate\Parser\CertificateParser;
use AcmePhp\Bundle\Acme\Certificate\Formatter\FormatterInterface;
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorageFactory;
use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;

/**
 * Persist and hydrate certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateRepository
{
    /** @var CertificateStorageFactory */
    protected $storageFactory;

    /** @var CertificateParser */
    protected $certificateParser;

    /** @var CertificateFormatter */
    protected $certificateFormatter;

    /** @var FormatterInterface[] */
    protected $extraFormatters;

    /**
     * @param CertificateStorageFactory $storageFactory
     * @param CertificateParser         $certificateParser
     * @param CertificateFormatter      $certificateFormatter
     * @param array                     $extraFormatters
     */
    public function __construct(
        CertificateStorageFactory $storageFactory,
        CertificateParser $certificateParser,
        CertificateFormatter $certificateFormatter,
        array $extraFormatters
    ) {
        $this->storageFactory = $storageFactory;
        $this->certificateParser = $certificateParser;
        $this->certificateFormatter = $certificateFormatter;
        $this->extraFormatters = $extraFormatters;

        if (!in_array($this->certificateFormatter, $this->extraFormatters)) {
            $this->extraFormatters[] = $this->certificateFormatter;
        }
    }

    /**
     * Store the given certificate in several formats.
     *
     * @param DomainConfiguration $configuration
     * @param Certificate         $certificate
     * @param KeyPair             $domainKeyPair
     */
    public function persistCertificate(
        DomainConfiguration $configuration,
        Certificate $certificate,
        KeyPair $domainKeyPair
    ) {
        $storage = $this->storageFactory->createCertificateStorage($configuration->getDomain());
        $storage->backup();
        /** @var FormatterInterface $formatter */
        foreach ($this->extraFormatters as $formatter) {
            $storage->saveCertificateFile($formatter->getName(), $formatter->format($certificate, $domainKeyPair));
        }
    }

    /**
     * Clear the persisted certificates.
     *
     * @param DomainConfiguration $configuration
     */
    public function clearCertificate(DomainConfiguration $configuration)
    {
        $storage = $this->storageFactory->createCertificateStorage($configuration->getDomain());
        $storage->removeCertificateFile($this->certificateFormatter->getName());
        /** @var FormatterInterface $formatter */
        foreach ($this->extraFormatters as $formatter) {
            $storage->removeCertificateFile($formatter->getName());
        }
    }

    /**
     * Return whether or not a certificate exists for the given configuration.
     *
     * @param DomainConfiguration $configuration
     *
     * @return bool
     */
    public function hasCertificate(DomainConfiguration $configuration)
    {
        $storage = $this->storageFactory->createCertificateStorage($configuration->getDomain());
        foreach ($this->extraFormatters as $formatter) {
            if (!$storage->hasCertificateFile($formatter->getName())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return certificate's data.
     *
     * @param DomainConfiguration $configuration
     *
     * @return CertificateMetadata
     */
    public function loadCertificate(DomainConfiguration $configuration)
    {
        $storage = $this->storageFactory->createCertificateStorage($configuration->getDomain());

        return $this->certificateParser->parse($storage->loadCertificateFile($this->certificateFormatter->getName()));
    }
}
