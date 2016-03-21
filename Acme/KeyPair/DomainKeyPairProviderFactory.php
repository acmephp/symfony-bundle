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

use AcmePhp\Bundle\Acme\KeyPair\Storage\DomainKeyPairStorageFactory;
use AcmePhp\Core\Ssl\KeyPairManager;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Create KeyPair providers for domain certificates.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class DomainKeyPairProviderFactory
{
    use LoggerAwareTrait;

    /** @var KeyPairManager */
    private $keyPairManager;

    /** @var DomainKeyPairStorageFactory */
    private $storageFactory;

    /***
     * @param KeyPairManager              $keyPairManager
     * @param DomainKeyPairStorageFactory $storageFactory
     */
    public function __construct(KeyPairManager $keyPairManager, DomainKeyPairStorageFactory $storageFactory)
    {
        $this->keyPairManager = $keyPairManager;
        $this->storageFactory = $storageFactory;

        $this->logger = new NullLogger();
    }

    /**
     * Create a new instance of KeyPairProvider for the given domain.
     *
     * @param string $domain
     *
     * @return KeyPairProvider
     */
    public function createKeyPairProvider($domain)
    {
        $provider = new KeyPairProvider(
            $this->keyPairManager,
            $this->storageFactory->createKeyPairStorage($domain)
        );
        $provider->setLogger($this->logger);

        return $provider;
    }
}
