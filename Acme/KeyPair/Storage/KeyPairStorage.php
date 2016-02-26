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

use AcmePhp\Core\Ssl\KeyPair;
use AcmePhp\Core\Ssl\KeyPairManager;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Storage for KeyPair.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class KeyPairStorage
{
    /** @var Filesystem */
    private $filesystem;

    /** @var KeyPairManager */
    private $keyPairManager;

    /** @var string */
    private $storagePath;

    /**
     * KeyPairStorage constructor.
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
     * Returns whether or not a keyPair exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->filesystem->exists([
            $this->getPublicFilePath(),
            $this->getPrivateFilePath(),
        ]);
    }

    /**
     * Stores the given KeyPair.
     *
     * @param \AcmePhp\Core\Ssl\KeyPair $keyPair
     */
    public function store(KeyPair $keyPair)
    {
        $this->filesystem->dumpFile($this->getPublicFilePath(), $keyPair->getPublicKeyAsPEM());
        $this->filesystem->dumpFile($this->getPrivateFilePath(), $keyPair->getPrivateKeyAsPEM());
    }

    /**
     * Retrieves the stored KeyPair.
     *
     * @return KeyPair
     */
    public function load()
    {
        return $this->keyPairManager->loadKeyPair(
            $this->getPublicFilePath(),
            $this->getPrivateFilePath()
        );
    }

    /**
     * Retrieves the storage location.
     *
     * @return string
     */
    public function getRootPath()
    {
        return $this->storagePath;
    }

    /**
     * Retrieves the path to the public key file.
     *
     * @return string
     */
    private function getPublicFilePath()
    {
        return $this->storagePath.DIRECTORY_SEPARATOR.'public.pem';
    }

    /**
     * Retrieves the path to the private key file.
     *
     * @return string
     */
    private function getPrivateFilePath()
    {
        return $this->storagePath.DIRECTORY_SEPARATOR.'private.pem';
    }
}
