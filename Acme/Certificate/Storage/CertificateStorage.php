<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate\Storage;

use AcmePhp\Bundle\Exception\CertificateFileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Certificates storage.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateStorage
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $storagePath;

    /** @var string */
    private $backupStoragePath;

    /**
     * @param Filesystem $filesystem
     * @param string     $storagePath
     * @param string     $backupStoragePath
     */
    public function __construct(Filesystem $filesystem, $storagePath, $backupStoragePath)
    {
        $this->filesystem = $filesystem;
        $this->storagePath = $storagePath;
        $this->backupStoragePath = $backupStoragePath;
    }

    /**
     * Backup the certificates files into the configured `backupStoragePath`.
     */
    public function backup()
    {
        $this->filesystem->mirror(
            $this->storagePath,
            $this->backupStoragePath.DIRECTORY_SEPARATOR.date('Ymd-His')
        );
    }

    /**
     * Returns whether or not a certificate file exists.
     *
     * @param string $certificateFileName
     *
     * @return bool
     */
    public function hasCertificateFile($certificateFileName)
    {
        return $this->filesystem->exists(
            $this->getCertificateFilePath($certificateFileName)
        );
    }

    /**
     * Returns the content of the certificate file.
     *
     * @param string $certificateFileName
     *
     * @return string
     */
    public function loadCertificateFile($certificateFileName)
    {
        if (!$this->hasCertificateFile($certificateFileName)) {
            throw new CertificateFileNotFoundException();
        }

        return file_get_contents($this->getCertificateFilePath($certificateFileName));
    }

    /**
     * Remove the given certificate file.
     *
     * @param string $certificateFileName
     */
    public function removeCertificateFile($certificateFileName)
    {
        $this->filesystem->remove($this->getCertificateFilePath($certificateFileName));
    }

    /**
     * Save the certificate file in the configured `storagePath`.
     *
     * @param string $certificateFileName
     * @param string $content
     */
    public function saveCertificateFile($certificateFileName, $content)
    {
        $this->filesystem->dumpFile($this->getCertificateFilePath($certificateFileName), $content);
    }

    /**
     * Retrieves the path to the certificate file.
     *
     * @param string $certificateFileName
     *
     * @return string
     */
    private function getCertificateFilePath($certificateFileName)
    {
        return $this->storagePath.DIRECTORY_SEPARATOR.$certificateFileName;
    }
}
