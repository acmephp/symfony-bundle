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
    /**
     * @var ClientInterface
     */
    private $httpClient;
    /**
     * @var Base64SafeEncoder
     */
    private $base64Encoder;
    /**
     * @var KeyParser
     */
    private $keyParser;
    /**
     * @var DataSigner
     */
    private $dataSigner;
    /**
     * @var ServerErrorHandler
     */
    private $errorHandler;

    /**
     * @param CertificateAuthorityConfigurationInterface $certificateAuthority
     * @param ClientInterface                            $httpClient
     * @param Base64SafeEncoder                          $base64Encoder
     * @param KeyParser                                  $keyParser
     * @param DataSigner                                 $dataSigner
     * @param ServerErrorHandler                         $errorHandler
     */
    public function __construct(
        CertificateAuthorityConfigurationInterface $certificateAuthority,
        ClientInterface $httpClient,
        Base64SafeEncoder $base64Encoder,
        KeyParser $keyParser,
        DataSigner $dataSigner,
        ServerErrorHandler $errorHandler
    ) {
        $this->certificateAuthority = $certificateAuthority;
        $this->httpClient = $httpClient;
        $this->base64Encoder = $base64Encoder;
        $this->keyParser = $keyParser;
        $this->dataSigner = $dataSigner;
        $this->errorHandler = $errorHandler;

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
            new SecureHttpClient(
                $accountKeyPair,
                $this->httpClient,
                $this->base64Encoder,
                $this->keyParser,
                $this->dataSigner,
                $this->errorHandler
            ),
            $this->certificateAuthority->getDirectoryUri()
        );
    }
}
