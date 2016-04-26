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

use AcmePhp\Bundle\Acme\Certificate\CertificateRepository;
use AcmePhp\Bundle\Acme\Certificate\Requester;
use AcmePhp\Bundle\Acme\Domain\Challenger;
use AcmePhp\Bundle\Acme\KeyPair\DomainKeyPairProviderFactory;
use AcmePhp\Bundle\Acme\KeyPair\KeyPairProvider;
use AcmePhp\Bundle\Event\CertificateResponseEvent;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\CertificateResponse;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\KeyPair;
use AcmePhp\Ssl\ParsedCertificate;
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
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();

        $this->mockCertificateRepository->hasCertificate($dummyDistinguishedName)->willReturn(false);
        $this->mockChallenger->challengeDomains([$dummyDomain])->shouldBeCalled();

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate(
            $dummyDomain,
            Argument::that(
                function ($item) use ($dummyDistinguishedName, $dummyDomainKeyPair) {
                    $this->assertInstanceOf(CertificateRequest::class, $item);
                    $this->assertSame($dummyDistinguishedName, $item->getDistinguishedName());
                    $this->assertSame($dummyDomainKeyPair, $item->getKeyPair());

                    return true;
                }
            )
        )->shouldBeCalled()->willReturn($dummyCertificateResponse);

        $result = $this->service->requestCertificate($dummyDistinguishedName);
        $this->assertSame($dummyCertificateResponse, $result);
    }

    public function test requestCertificate dont triggers a new challenge when needed()
    {
        $dummyDomain = uniqid();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyParsedCertificate = new ParsedCertificate(
            $dummyCertificate,
            $dummyDomain,
            null,
            true,
            null,
            null,
            null,
            [$dummyDomain]
        );

        $this->mockCertificateRepository->hasCertificate($dummyDistinguishedName)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($dummyDistinguishedName)->willReturn($dummyParsedCertificate);
        $this->mockChallenger->challengeDomains()->shouldNotBeCalled();

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, Argument::any())->shouldBeCalled()->willReturn(
            $dummyCertificateResponse
        );

        $result = $this->service->requestCertificate($dummyDistinguishedName);
        $this->assertSame($dummyCertificateResponse, $result);
    }

    public function test requestCertificate triggers a new challenge for extra domains()
    {
        $dummyDomain = uniqid();
        $dummyAlternativeDomain = uniqid();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyParsedCertificate = new ParsedCertificate(
            $dummyCertificate,
            $dummyDomain,
            null,
            true,
            null,
            null,
            null,
            [$dummyDomain]
        );
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();
        $dummyDistinguishedName = new DistinguishedName(
            $dummyDomain,
            null,
            null,
            null,
            null,
            null,
            null,
            [$dummyDomain, $dummyAlternativeDomain]
        );

        $this->mockCertificateRepository->hasCertificate($dummyDistinguishedName)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($dummyDistinguishedName)->willReturn($dummyParsedCertificate);
        $this->mockChallenger->challengeDomains([$dummyAlternativeDomain])->shouldBeCalled();

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, Argument::any())->shouldBeCalled()->willReturn(
            $dummyCertificateResponse
        );

        $result = $this->service->requestCertificate($dummyDistinguishedName);
        $this->assertSame($dummyCertificateResponse, $result);
    }

    public function test requestCertificate notice the logger()
    {
        $dummyDomain = uniqid();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyParsedCertificate = new ParsedCertificate(
            $dummyCertificate,
            $dummyDomain,
            null,
            true,
            null,
            null,
            null,
            [$dummyDomain]
        );
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);

        $this->mockCertificateRepository->hasCertificate($dummyDistinguishedName)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($dummyDistinguishedName)->willReturn($dummyParsedCertificate);

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, Argument::any())->shouldBeCalled()->willReturn(
            $dummyCertificateResponse
        );

        $mockLogger = $this->prophesize(LoggerInterface::class);
        $this->service->setLogger($mockLogger->reveal());
        $mockLogger->notice(
            Argument::containingString('Certificate for domain {domain} requested'),
            ['domain' => $dummyDomain]
        )->shouldBeCalled();

        $this->service->requestCertificate($dummyDistinguishedName);
    }

    public function test requestCertificate trigger event()
    {
        $dummyDomain = uniqid();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyParsedCertificate = new ParsedCertificate(
            $dummyCertificate,
            $dummyDomain,
            null,
            true,
            null,
            null,
            null,
            [$dummyDomain]
        );
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);

        $this->mockCertificateRepository->hasCertificate($dummyDistinguishedName)->willReturn(true);
        $this->mockCertificateRepository->loadCertificate($dummyDistinguishedName)->willReturn($dummyParsedCertificate);

        $mockKeyPairProvider = $this->prophesize(KeyPairProvider::class);
        $this->mockKeyPairFactory->createKeyPairProvider($dummyDomain)->shouldBeCalled()->willReturn(
            $mockKeyPairProvider
        );
        $mockKeyPairProvider->getOrCreateKeyPair()->shouldBeCalled()->willReturn($dummyDomainKeyPair);

        $this->mockClient->requestCertificate($dummyDomain, Argument::any())->shouldBeCalled()->willReturn(
            $dummyCertificateResponse
        );

        $this->mockDispatcher->dispatch(
            'acme_php.certificate.requested',
            Argument::that(
                function ($item) use ($dummyCertificateResponse) {
                    $this->assertInstanceOf(CertificateResponseEvent::class, $item);
                    $this->assertSame($dummyCertificateResponse, $item->getCertificateResponse());

                    return true;
                }
            )
        )->shouldBeCalled();

        $this->service->requestCertificate($dummyDistinguishedName);
    }
}
