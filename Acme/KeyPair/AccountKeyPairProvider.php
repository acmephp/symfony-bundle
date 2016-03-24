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

use AcmePhp\Bundle\Acme\CertificateAuthority\ClientFactory;
use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Core\Ssl\KeyPair;
use AcmePhp\Core\Ssl\KeyPairManager;

/**
 * KeyPairs provider.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class AccountKeyPairProvider extends KeyPairProvider
{
    /** @var ClientFactory */
    private $clientFactory;

    /** @var string */
    private $contactEmail;

    /**
     * @param KeyPairManager $manager
     * @param KeyPairStorage $storage
     * @param ClientFactory  $clientFactory
     * @param string         $contactEmail
     */
    public function __construct(
        KeyPairManager $manager,
        KeyPairStorage $storage,
        ClientFactory $clientFactory,
        $contactEmail
    ) {
        parent::__construct($manager, $storage);

        $this->clientFactory = $clientFactory;
        $this->contactEmail = $contactEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function storeKeyPair(KeyPair $keyPair)
    {
        $this->register($keyPair);

        parent::storeKeyPair($keyPair);
    }

    /**
     * Register the new keyPair to the Certificate Authority.
     *
     * @param KeyPair $keyPair
     */
    public function register(KeyPair $keyPair)
    {
        $client = $this->clientFactory->createAcmeClient($keyPair);
        $client->registerAccount($this->contactEmail);

        $this->logger->notice('Account {contactEmail} registered', ['contactEmail' => $this->contactEmail]);
    }
}
