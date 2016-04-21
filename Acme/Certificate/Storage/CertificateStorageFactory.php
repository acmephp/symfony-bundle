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

use Symfony\Component\Filesystem\Filesystem;

/**
 * Create CertificateStorage.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateStorageFactory
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $storagePath;

    /** @var string */
    private $backupStoragePath;

    /**
     * CertificateStorageFactory constructor.
     *
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
     * Create a new instance of CertificateStorage for the given domain.
     *
     * @param string $commonName
     *
     * @return CertificateStorage
     */
    public function createCertificateStorage($commonName)
    {
        return new CertificateStorage(
            $this->filesystem,
            $this->storagePath.DIRECTORY_SEPARATOR.$commonName,
            $this->backupStoragePath.DIRECTORY_SEPARATOR.$commonName
        );
    }
}
