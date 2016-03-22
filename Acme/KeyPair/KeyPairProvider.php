<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\KeyPair;

use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Core\Ssl\KeyPair;
use AcmePhp\Core\Ssl\KeyPairManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * KeyPairs provider.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class KeyPairProvider
{
    use LoggerAwareTrait;

    /** @var KeyPairManager */
    protected $manager;

    /** @var KeyPairStorage */
    protected $storage;

    /**
     * @param KeyPairManager $manager
     * @param KeyPairStorage $storage
     */
    public function __construct(KeyPairManager $manager, KeyPairStorage $storage)
    {
        $this->manager = $manager;
        $this->storage = $storage;

        $this->logger = new NullLogger();
    }

    /**
     * Retrieves an existing keyPair, otherwise create a new one and returns it.
     *
     * @return KeyPair
     */
    public function getOrCreateKeyPair()
    {
        if (!$this->hasKeyPair()) {
            $this->createKeyPair();
        }

        return $this->getKeyPair();
    }

    /**
     * Returns whether or not a keyPair exists.
     *
     * @return bool
     */
    public function hasKeyPair()
    {
        return $this->storage->exists();
    }

    /**
     * Gets the existing keyPair.
     *
     * @return KeyPair
     */
    public function getKeyPair()
    {
        return $this->storage->load();
    }

    /**
     * Creates a new keyPair.
     */
    public function createKeyPair()
    {
        $this->logger->info(
            'Generating new KeyPair in {storageLocation}',
            [
                'storageLocation' => $this->storage->getRootPath(),
            ]
        );

        $this->storeKeyPair($this->manager->generateKeyPair());
    }

    /**
     * Store the given keyPair.
     *
     * @param KeyPair $keyPair
     */
    public function storeKeyPair(KeyPair $keyPair)
    {
        $this->storage->store($keyPair);
    }
}
