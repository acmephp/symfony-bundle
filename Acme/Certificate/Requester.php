<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate;

use AcmePhp\Bundle\Acme\Domain\Challenger;
use AcmePhp\Bundle\Acme\KeyPair\DomainKeyPairProviderFactory;
use AcmePhp\Bundle\Event\CertificateResponseEvent;
use AcmePhp\Bundle\Event\AcmePhpBundleEvents;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\CertificateResponse;
use AcmePhp\Ssl\DistinguishedName;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Certificates requester.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class Requester
{
    use LoggerAwareTrait;

    /** @var AcmeClient */
    private $client;

    /** @var DomainKeyPairProviderFactory */
    private $keyPairFactory;

    /** @var CertificateRepository */
    private $certificateRepository;

    /** @var Challenger */
    private $challenger;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /**
     * @param AcmeClient                   $client
     * @param DomainKeyPairProviderFactory $keyPairFactory
     * @param Challenger                   $challenger
     * @param CertificateRepository        $certificateRepository
     * @param EventDispatcherInterface     $dispatcher
     */
    public function __construct(
        AcmeClient $client,
        DomainKeyPairProviderFactory $keyPairFactory,
        Challenger $challenger,
        CertificateRepository $certificateRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->client = $client;
        $this->keyPairFactory = $keyPairFactory;
        $this->challenger = $challenger;
        $this->certificateRepository = $certificateRepository;
        $this->dispatcher = $dispatcher;

        $this->logger = new NullLogger();
    }

    /**
     * Request a new certificate for the given configuration.
     *
     * @param DistinguishedName $distinguishedName
     *
     * @return CertificateResponse
     */
    public function requestCertificate(DistinguishedName $distinguishedName)
    {
        $subjectNames = array_merge(
            [$distinguishedName->getCommonName()],
            (array) $distinguishedName->getSubjectAlternativeNames()
        );

        $challengedNames = [];
        if ($this->certificateRepository->hasCertificate($distinguishedName)) {
            $parsedCertificate = $this->certificateRepository->loadCertificate($distinguishedName);
            $challengedNames = array_merge(
                [$distinguishedName->getCommonName()],
                $parsedCertificate->getSubjectAlternativeNames()
            );
        }

        $unchallengedDomains = array_values(array_diff($subjectNames, $challengedNames));
        if (count($unchallengedDomains)) {
            $this->challenger->challengeDomains($unchallengedDomains);
        }

        $commonName = $distinguishedName->getCommonName();
        $domainKeyPair = $this->keyPairFactory->createKeyPairProvider($commonName)->getOrCreateKeyPair();

        $certificateResponse = $this->client->requestCertificate(
            $commonName,
            new CertificateRequest(
                $distinguishedName,
                $domainKeyPair
            )
        );

        $this->dispatcher->dispatch(
            AcmePhpBundleEvents::CERTIFICATE_REQUESTED,
            new CertificateResponseEvent($certificateResponse)
        );

        $this->logger->notice(
            'Certificate for domain {domain} requested',
            ['domain' => $distinguishedName->getCommonName()]
        );

        return $certificateResponse;
    }
}
