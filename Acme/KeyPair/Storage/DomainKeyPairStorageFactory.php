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

use AcmePhp\Core\Ssl\KeyPairManager;
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

    /** @var KeyPairManager */
    private $keyPairManager;

    /** @var string */
    private $storagePath;

    /**
     * DomainKeyPairStorageFactory constructor.
     *
     * @param Filesystem     $filesystem
     * @param KeyPairManager $keyPairManager
     * @param string         $storagePath
     */
    public function __construct(Filesystem $filesystem, KeyPairManager $keyPairManager, $storagePath)
    {
        $this->filesystem = $filesystem;
        $this->keyPairManager = $keyPairManager;
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
            $this->keyPairManager,
            $this->storagePath.DIRECTORY_SEPARATOR.$domain
        );
    }
}
