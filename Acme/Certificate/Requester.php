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
use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Bundle\Acme\KeyPair\DomainKeyPairProviderFactory;
use AcmePhp\Bundle\Event\CertificateEvent;
use AcmePhp\Bundle\Event\AcmePhpBundleEvents;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Ssl\Certificate;
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
     * @param DomainConfiguration $configuration
     *
     * @return Certificate
     */
    public function requestCertificate(DomainConfiguration $configuration)
    {
        $domains = array_merge([$configuration->getDomain()], (array) $configuration->getCSR()->getSubjectAlternativeNames());
        $challengedDomains = [];
        if ($this->certificateRepository->hasCertificate($configuration)) {
            $data = $this->certificateRepository->loadCertificate($configuration);
            $challengedDomains = array_merge([$configuration->getDomain()], $data['subjectAlternativeNames']);
        }

        $unchallengedDomains = array_values(array_diff($domains, $challengedDomains));
        if (count($unchallengedDomains)) {
            $this->challenger->challengeDomains($unchallengedDomains);
        }

        $domain = $configuration->getDomain();
        $domainKeyPair = $this->keyPairFactory->createKeyPairProvider($domain)->getOrCreateKeyPair();

        $certificate = $this->client->requestCertificate(
            $domain,
            $domainKeyPair,
            $configuration->getCSR()
        );

        $this->dispatcher->dispatch(
            AcmePhpBundleEvents::CERTIFICATE_REQUESTED,
            new CertificateEvent(
                $configuration,
                $certificate,
                $domainKeyPair
            )
        );

        $this->logger->notice('Certificate for domain {domain} requested', ['domain' => $configuration->getDomain()]);

        return $certificate;
    }
}
