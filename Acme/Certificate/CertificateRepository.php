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

use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorage;
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorageFactory;
use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\CertificateResponse;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Formatter\CertificateFormatter;
use AcmePhp\Ssl\Formatter\FormatterInterface;
use AcmePhp\Ssl\ParsedCertificate;
use AcmePhp\Ssl\Parser\CertificateParser;
use Webmozart\Assert\Assert;

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

    /** @var string */
    protected $certificateFilename;

    /** @var FormatterInterface[] */
    protected $extraFormatters;

    /**
     * @param CertificateStorageFactory $storageFactory
     * @param CertificateParser         $certificateParser
     * @param CertificateFormatter      $certificateFormatter
     * @param string                    $certificateFilename
     */
    public function __construct(
        CertificateStorageFactory $storageFactory,
        CertificateParser $certificateParser,
        CertificateFormatter $certificateFormatter,
        $certificateFilename
    ) {
        $this->storageFactory = $storageFactory;
        $this->certificateParser = $certificateParser;
        $this->certificateFormatter = $certificateFormatter;
        $this->certificateFilename = $certificateFilename;

        $this->addFormatter($certificateFilename, $certificateFormatter);
    }

    public function addFormatter($filename, FormatterInterface $formatter)
    {
        Assert::stringNotEmpty($filename, __FUNCTION__.'::$filename expected an non-empty string. Got: %s');
        if (isset($this->extraFormatters[$filename])) {
            Assert::isInstanceOf(
                $formatter,
                get_class($this->extraFormatters[$filename]),
                'An differente instance of formatter already given for this filename. Got: %s'
            );

            return;
        }

        $this->extraFormatters[$filename] = $formatter;
    }

    /**
     * Store the given certificate in several formats.
     *
     * @param DistinguishedName   $distinguishedName
     * @param CertificateResponse $certificateResponse
     */
    public function persistCertificate(DistinguishedName $distinguishedName, CertificateResponse $certificateResponse)
    {
        $storage = $this->getCertificateStorage($distinguishedName);
        $storage->backup();

        /** @var FormatterInterface $formatter */
        foreach ($this->extraFormatters as $filename => $formatter) {
            $storage->saveCertificateFile($filename, $formatter->format($certificateResponse));
        }
    }

    /**
     * Clear the persisted certificates.
     *
     * @param DistinguishedName $distinguishedName
     */
    public function clearCertificate(DistinguishedName $distinguishedName)
    {
        $storage = $this->getCertificateStorage($distinguishedName);

        /** @var FormatterInterface $formatter */
        foreach ($this->extraFormatters as $filename => $formatter) {
            $storage->removeCertificateFile($filename);
        }
    }

    /**
     * Return whether or not a certificate fully exists for the given configuration.
     *
     * @param DistinguishedName $distinguishedName
     *
     * @return bool
     */
    public function hasCertificate(DistinguishedName $distinguishedName)
    {
        $storage = $this->getCertificateStorage($distinguishedName);

        /** @var FormatterInterface $formatter */
        foreach ($this->extraFormatters as $filename => $formatter) {
            if (!$storage->hasCertificateFile($filename)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return certificate's data.
     *
     * @param DistinguishedName $distinguishedName
     *
     * @return ParsedCertificate
     */
    public function loadCertificate(DistinguishedName $distinguishedName)
    {
        $storage = $this->getCertificateStorage($distinguishedName);

        return $this->certificateParser->parse(
            new Certificate($storage->loadCertificateFile($this->certificateFilename))
        );
    }

    /**
     * Retrieves a CertificateStorage for the commonName contains in the given distinguishedName.
     *
     * @param DistinguishedName $distinguishedName
     *
     * @return CertificateStorage
     */
    private function getCertificateStorage(DistinguishedName $distinguishedName)
    {
        return $this->storageFactory->createCertificateStorage($distinguishedName->getCommonName());
    }
}
