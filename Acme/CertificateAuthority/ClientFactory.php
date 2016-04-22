<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\CertificateAuthority;

use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Http\Base64SafeEncoder;
use AcmePhp\Core\Http\SecureHttpClient;
use AcmePhp\Core\Http\SecureHttpClientFactory;
use AcmePhp\Core\Http\ServerErrorHandler;
use AcmePhp\Ssl\KeyPair;
use AcmePhp\Ssl\Parser\KeyParser;
use AcmePhp\Ssl\Signer\DataSigner;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Create Acme Certificate Authority clients.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ClientFactory
{
    use LoggerAwareTrait;

    /** @var CertificateAuthorityConfigurationInterface */
    private $certificateAuthority;

    /** @var SecureHttpClientFactory */
    private $secureHttpClientFactory;

    /**
     * @param CertificateAuthorityConfigurationInterface $certificateAuthority
     * @param SecureHttpClientFactory                    $secureHttpClientFactory
     */
    public function __construct(
        CertificateAuthorityConfigurationInterface $certificateAuthority,
        SecureHttpClientFactory $secureHttpClientFactory
    ) {
        $this->certificateAuthority = $certificateAuthority;
        $this->secureHttpClientFactory = $secureHttpClientFactory;

        $this->logger = new NullLogger();
    }

    /**
     * Create a new instance of AcmeClient for the given account KeyPair.
     *
     * @param KeyPair $accountKeyPair
     *
     * @return AcmeClient
     */
    public function createAcmeClient(KeyPair $accountKeyPair)
    {
        return new AcmeClient(
            $this->secureHttpClientFactory->createSecureHttpClient($accountKeyPair),
            $this->certificateAuthority->getDirectoryUri()
        );
    }
}
