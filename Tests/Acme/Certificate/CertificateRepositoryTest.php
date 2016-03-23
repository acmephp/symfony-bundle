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
use AcmePhp\Bundle\Acme\Certificate\Formatter\CertificateFormatter;
use AcmePhp\Bundle\Acme\Certificate\Parser\CertificateParser;
use AcmePhp\Bundle\Acme\Certificate\Formatter\FormatterInterface;
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorage;
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorageFactory;
use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\CSR;
use AcmePhp\Core\Ssl\KeyPair;

class CertificateRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var CertificateRepository */
    private $service;

    /** @var CertificateStorageFactory */
    private $mockStorageFactory;

    /** @var CertificateFormatter */
    private $mockCertificateFormatter;

    /** @var FormatterInterface */
    private $mockExtraFormatter;

    /** @var CertificateParser */
    private $mockCertificateParser;

    public function setUp()
    {
        parent::setUp();

        $this->mockStorageFactory = $this->prophesize(CertificateStorageFactory::class);
        $this->mockExtraFormatter = $this->prophesize(FormatterInterface::class);
        $this->mockCertificateFormatter = $this->prophesize(CertificateFormatter::class);
        $this->mockCertificateParser = $this->prophesize(CertificateParser::class);

        $this->service = new CertificateRepository(
            $this->mockStorageFactory->reveal(),
            $this->mockCertificateParser->reveal(),
            $this->mockCertificateFormatter->reveal(),
            [$this->mockExtraFormatter->reveal()]
        );
    }

    public function test persistCertificate backups the previous certificates()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());
        $mockStorage->backup()->shouldBeCalled();
        $mockStorage->saveCertificateFile(null, null)->shouldBeCalled();

        $this->service->persistCertificate($configuration, $dummyCertificate, $dummyDomainKeyPair);
    }

    public function test persistCertificate persists the certificate file()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateFileName = uniqid();
        $dummyExtraFileName = uniqid();
        $dummyCertificateFileContent = uniqid();
        $dummyExtaFileContent = uniqid();

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());
        $mockStorage->backup()->shouldBeCalled();

        $this->mockCertificateFormatter->getName()->willReturn($dummyCertificateFileName);
        $this->mockCertificateFormatter->format($dummyCertificate, $dummyDomainKeyPair)->willReturn($dummyCertificateFileContent);
        $this->mockExtraFormatter->getName()->willReturn($dummyExtraFileName);
        $this->mockExtraFormatter->format($dummyCertificate, $dummyDomainKeyPair)->willReturn($dummyExtaFileContent);
        $mockStorage->saveCertificateFile($dummyCertificateFileName, $dummyCertificateFileContent)->shouldBeCalled();
        $mockStorage->saveCertificateFile($dummyExtraFileName, $dummyExtaFileContent)->shouldBeCalled();

        $this->service->persistCertificate($configuration, $dummyCertificate, $dummyDomainKeyPair);
    }

    public function test clearCertificate remove persisted files()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyCertificateFileName = uniqid();
        $dummyExtraFileName = uniqid();

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());

        $this->mockCertificateFormatter->getName()->willReturn($dummyCertificateFileName);
        $this->mockCertificateFormatter->format()->shouldNotBeCalled();
        $this->mockExtraFormatter->getName()->willReturn($dummyExtraFileName);
        $this->mockExtraFormatter->format()->shouldNotBeCalled();
        $mockStorage->removeCertificateFile($dummyCertificateFileName)->shouldBeCalled();
        $mockStorage->removeCertificateFile($dummyExtraFileName)->shouldBeCalled();

        $this->service->clearCertificate($configuration);
    }

    public function test hasCertificate checks if files exists()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyCertificateFileName = uniqid();
        $dummyExtraFileName = uniqid();

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());

        $this->mockCertificateFormatter->getName()->willReturn($dummyCertificateFileName);
        $this->mockCertificateFormatter->format()->shouldNotBeCalled();
        $this->mockExtraFormatter->getName()->willReturn($dummyExtraFileName);
        $this->mockExtraFormatter->format()->shouldNotBeCalled();
        $mockStorage->hasCertificateFile($dummyCertificateFileName)->shouldBeCalled()->willReturn(true);
        $mockStorage->hasCertificateFile($dummyExtraFileName)->shouldBeCalled()->willReturn(true);

        $result = $this->service->hasCertificate($configuration);

        $this->assertTrue($result);
    }

    public function test loadCertificate use parsers to parse certificate file content()
    {
        $dummyDomain = uniqid();
        $dummyCsr = $this->prophesize(CSR::class)->reveal();
        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyDomainKeyPair = $this->prophesize(KeyPair::class)->reveal();
        $dummyCertificateFileContent = uniqid();
        $dummyCertificateMetadata = new CertificateMetadata(
            $dummyDomain,
            uniqid(),
            (bool) rand(0, 1),
            uniqid(),
            [uniqid()]
        );
        $dummyCertificateFileName = uniqid();

        $configuration = new DomainConfiguration($dummyDomain, $dummyCsr);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());

        $this->mockCertificateFormatter->getName()->willReturn($dummyCertificateFileName);
        $this->mockCertificateParser->parse($dummyCertificateFileContent)->shouldBeCalled()->willReturn(
            $dummyCertificateMetadata
        );
        $mockStorage->loadCertificateFile($dummyCertificateFileName)->shouldBeCalled()->willReturn(
            $dummyCertificateFileContent
        );

        $result = $this->service->loadCertificate($configuration, $dummyCertificate, $dummyDomainKeyPair);

        $this->assertSame(var_export($dummyCertificateMetadata, true), var_export($result, true));
    }
}
