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
use AcmePhp\Ssl\Generator\KeyPairGenerator;
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

    /** @var KeyPairGenerator */
    private $keyPairGenerator;

    /** @var DomainKeyPairStorageFactory */
    private $storageFactory;

    /***
     * @param KeyPairGenerator            $keyPairGenerator
     * @param DomainKeyPairStorageFactory $storageFactory
     */
    public function __construct(KeyPairGenerator $keyPairGenerator, DomainKeyPairStorageFactory $storageFactory)
    {
        $this->keyPairGenerator = $keyPairGenerator;
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
            $this->keyPairGenerator,
            $this->storageFactory->createKeyPairStorage($domain)
        );
        $provider->setLogger($this->logger);

        return $provider;
    }
}
