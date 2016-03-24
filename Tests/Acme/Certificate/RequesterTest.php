<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\Certificate;

use AcmePhp\Bundle\Acme\Certificate\CertificateMetadata;
use AcmePhp\Bundle\Acme\Certificate\CertificateRepository;
use AcmePhp\Bundle\Acme\Certificate\Requester;
use AcmePhp\Bundle\Acme\Domain\Challenger;
use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Bundle\Acme\KeyPair\DomainKeyPairProviderFactory;
use AcmePhp\Bundle\Acme\KeyPair\KeyPairProvider;
use AcmePhp\Bundle\Event\CertificateEvent;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\CSR;
use AcmePhp\Core\Ssl\KeyPair;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RequesterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Requester */
    private $service;

    /** @var AcmeClient */
    private $mockClient;

    /** @var DomainKeyPairProviderFactory */
    private $mockKeyPairFactory;

    /** @var Challenger */
    private $mockChallenger;

    /** @var CertificateRepository */
    private $mockCertificateRepository;

    /** @var EventDispatcherInterface */
    private $mockDispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->mockClient = $this->prophesize(AcmeClient::class);
        $this->mockKeyPairFactory = $this->prophesize(DomainKeyPairProviderFactory::class);
        $this->mockChallenger = $this->prophesize(Challenger::class);
        $this->mockCertificateRepository = $this->prophesize(CertificateRepository::class);
        $this->mockDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->service = new Requester(
            $this->mockClient->reveal(),
            $this->mockKeyPairFactory->reveal(),
            $this->mockChallenger->reveal(),
            $this->mockCertificateRepository->reveal(),
            $this->mockDispatcher->reveal()
        );
    }

    public function test requestCertificate triggers a new challenge when needed()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $this->mockCertificateRepository->hasCertificate($configuration)->willReturn(false);
        $this->mockChallenger->challengeDomains([$dummyDomain])->shouldBeCalled();

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, $dummyDomainKeyPair, $dummyCsr)->shouldBeCalled(
        )->willReturn($dummyCertificate);

        $result = $this->service->requestCertificate($configuration);
        $this->assertSame($dummyCertificate, $result);
    }

    public function test requestCertificate dont triggers a new challenge when needed()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyMetadata = new CertificateMetadata($dummyDomain, null, true, null, [$dummyDomain]);

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $this->mockCertificateRepository->hasCertificate($configuration)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($configuration)->willReturn($dummyMetadata);
        $this->mockChallenger->challengeDomains()->shouldNotBeCalled();

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, $dummyDomainKeyPair, $dummyCsr)->shouldBeCalled(
        )->willReturn($dummyCertificate);

        $result = $this->service->requestCertificate($configuration);
        $this->assertSame($dummyCertificate, $result);
    }

    public function test requestCertificate triggers a new challenge for extra domains()
    {
        $dummyDomain = uniqid();
        $dummyAlternativeDomain = uniqid();
        $mockCsr = $this->prophesize(CSR::class);
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyMetadata = new CertificateMetadata($dummyDomain, null, true, null, [$dummyDomain]);

        $mockCsr->getSubjectAlternativeNames()->willReturn([$dummyDomain, $dummyAlternativeDomain]);

        $configuration = new DomainConfiguration($dummyDomain, $mockCsr->reveal());

        $this->mockCertificateRepository->hasCertificate($configuration)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($configuration)->willReturn($dummyMetadata);
        $this->mockChallenger->challengeDomains([$dummyAlternativeDomain])->shouldBeCalled();

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, $dummyDomainKeyPair, $mockCsr->reveal())->shouldBeCalled(
        )->willReturn($dummyCertificate);

        $result = $this->service->requestCertificate($configuration);
        $this->assertSame($dummyCertificate, $result);
    }

    public function test requestCertificate notice the logger()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyMetadata = new CertificateMetadata($dummyDomain, null, true, null, [$dummyDomain]);

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $this->mockCertificateRepository->hasCertificate($configuration)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($configuration)->willReturn($dummyMetadata);

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, $dummyDomainKeyPair, $dummyCsr)->shouldBeCalled(
        )->willReturn($dummyCertificate);

        $mockLogger = $this->prophesize(LoggerInterface::class);
        $this->service->setLogger($mockLogger->reveal());
        $mockLogger->notice(
            Argument::containingString('Certificate for domain {domain} requested'),
            ['domain' => $dummyDomain]
        )->shouldBeCalled();

        $this->service->requestCertificate($configuration);
    }

    public function test requestCertificate trigger event()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyMetadata = new CertificateMetadata($dummyDomain, null, true, null, [$dummyDomain]);

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $this->mockCertificateRepository->hasCertificate($configuration)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($configuration)->willReturn($dummyMetadata);

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, $dummyDomainKeyPair, $dummyCsr)->shouldBeCalled(
        )->willReturn($dummyCertificate);

        $this->mockDispatcher->dispatch(
            'acme_php.certificate.requested',
            Argument::that(
                function ($item) use ($dummyCertificate) {
                    return $item instanceof CertificateEvent
                    && $item->getCertificate() === $dummyCertificate;
                }
            )
        )->shouldBeCalled();

        $this->service->requestCertificate($configuration);
    }
}
