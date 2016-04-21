<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\KeyPair\Storage;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Create KeyPair storage for domain certificates.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class DomainKeyPairStorageFactory
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $storagePath;

    /**
     * DomainKeyPairStorageFactory constructor.
     *
     * @param Filesystem $filesystem
     * @param string     $storagePath
     */
    public function __construct(Filesystem $filesystem, $storagePath)
    {
        $this->filesystem = $filesystem;
        $this->storagePath = $storagePath;
    }

    /**
     * Create a new instance of KeyPairStorage for the given domain.
     *
     * @param string $domain
     *
     * @return KeyPairStorage
     */
    public function createKeyPairStorage($domain)
    {
        return new KeyPairStorage(
            $this->filesystem,
            $this->storagePath.DIRECTORY_SEPARATOR.$domain
        );
    }
}
