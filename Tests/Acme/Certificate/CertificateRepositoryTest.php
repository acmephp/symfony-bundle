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
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorage;
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorageFactory;
use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\CertificateResponse;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\Formatter\CertificateFormatter;
use AcmePhp\Ssl\Formatter\FormatterInterface;
use AcmePhp\Ssl\Formatter\KeyPairFormatter;
use AcmePhp\Ssl\ParsedCertificate;
use AcmePhp\Ssl\Parser\CertificateParser;
use Prophecy\Argument;

class CertificateRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var CertificateRepository */
    private $service;

    /** @var CertificateStorageFactory */
    private $mockStorageFactory;

    /** @var CertificateFormatter */
    private $mockCertificateFormatter;

    /** @var string */
    private $dummyCertificateFilename;

    /** @var FormatterInterface */
    private $mockExtraFormatter;

    /** @var string */
    private $dummyExtraFilename;

    /** @var CertificateParser */
    private $mockCertificateParser;

    public function setUp()
    {
        parent::setUp();

        $this->mockStorageFactory = $this->prophesize(CertificateStorageFactory::class);
        $this->mockCertificateParser = $this->prophesize(CertificateParser::class);
        $this->mockExtraFormatter = $this->prophesize(FormatterInterface::class);
        $this->mockCertificateFormatter = $this->prophesize(CertificateFormatter::class);
        $this->dummyCertificateFilename = uniqid();
        $this->dummyExtraFilename = uniqid();

        $this->service = new CertificateRepository(
            $this->mockStorageFactory->reveal(),
            $this->mockCertificateParser->reveal(),
            $this->mockCertificateFormatter->reveal(),
            $this->dummyCertificateFilename
        );
        $this->service->addFormatter($this->dummyExtraFilename, $this->mockExtraFormatter->reveal());
    }

    public function test persistCertificate backups the previous certificates()
    {
        $dummyDomain = uniqid();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());
        $mockStorage->backup()->shouldBeCalled();
        $mockStorage->saveCertificateFile($this->dummyCertificateFilename, null)->shouldBeCalled();
        $mockStorage->saveCertificateFile($this->dummyExtraFilename, null)->shouldBeCalled();

        $this->service->persistCertificate($dummyDistinguishedName, $dummyCertificateResponse);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A different instance of formatter already given for this filename. Got: AcmePhp\Ssl\Formatter\KeyPairFormatter
     */
    public function test extraFormatters raise exception when two formatter use the same filename()
    {
        $formatterA = new CertificateFormatter();
        $formatterB = new KeyPairFormatter();

        $this->service->addFormatter('foo.pem', $formatterA);
        $this->service->addFormatter('foo.pem', $formatterB);
    }

    public function test extraFormatters allow two identical formatter with the same filename()
    {
        $formatterA = new CertificateFormatter();
        $formatterB = new CertificateFormatter();

        $this->service->addFormatter('foo.pem', $formatterA);
        $this->service->addFormatter('foo.pem', $formatterB);
    }

    public function test persistCertificate persists the certificate file()
    {
        $dummyDomain = uniqid();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);
        $dummyCertificateResponse = $this->prophesize(CertificateResponse::class)->reveal();
        $dummyCertificateFileContent = uniqid();
        $dummyExtaFileContent = uniqid();

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());
        $mockStorage->backup()->shouldBeCalled();

        $this->mockCertificateFormatter->format($dummyCertificateResponse)->willReturn($dummyCertificateFileContent);
        $this->mockExtraFormatter->format($dummyCertificateResponse)->willReturn($dummyExtaFileContent);
        $mockStorage->saveCertificateFile(
            $this->dummyCertificateFilename,
            $dummyCertificateFileContent
        )->shouldBeCalled();
        $mockStorage->saveCertificateFile($this->dummyExtraFilename, $dummyExtaFileContent)->shouldBeCalled();

        $this->service->persistCertificate($dummyDistinguishedName, $dummyCertificateResponse);
    }

    public function test clearCertificate remove persisted files()
    {
        $dummyDomain = uniqid();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());

        $this->mockCertificateFormatter->format()->shouldNotBeCalled();
        $this->mockExtraFormatter->format()->shouldNotBeCalled();
        $mockStorage->removeCertificateFile($this->dummyCertificateFilename)->shouldBeCalled();
        $mockStorage->removeCertificateFile($this->dummyExtraFilename)->shouldBeCalled();

        $this->service->clearCertificate($dummyDistinguishedName);
    }

    public function test hasCertificate checks if files exists()
    {
        $dummyDomain = uniqid();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());

        $this->mockCertificateFormatter->format()->shouldNotBeCalled();
        $this->mockExtraFormatter->format()->shouldNotBeCalled();
        $mockStorage->hasCertificateFile($this->dummyCertificateFilename)->shouldBeCalled()->willReturn(true);
        $mockStorage->hasCertificateFile($this->dummyExtraFilename)->shouldBeCalled()->willReturn(true);

        $result = $this->service->hasCertificate($dummyDistinguishedName);

        $this->assertTrue($result);
    }

    public function test loadCertificate use parsers to parse certificate file content()
    {
        $dummyDomain = uniqid();
        $dummyDistinguishedName = new DistinguishedName($dummyDomain);
        $dummyCertificateFileContent = uniqid();

        $dummyParsedCertificate = new ParsedCertificate(
            new Certificate($dummyCertificateFileContent),
            $dummyDomain
        );

        $mockStorage = $this->prophesize(CertificateStorage::class);
        $this->mockStorageFactory->createCertificateStorage($dummyDomain)->willReturn($mockStorage->reveal());

        $this->mockCertificateParser->parse(
            Argument::that(
                function ($item) use ($dummyCertificateFileContent) {
                    $this->assertInstanceOf(Certificate::class, $item);
                    $this->assertSame($dummyCertificateFileContent, $item->getPEM());

                    return true;
                }
            )
        )->shouldBeCalled()->willReturn($dummyParsedCertificate);
        $mockStorage->loadCertificateFile($this->dummyCertificateFilename)->shouldBeCalled()->willReturn(
            $dummyCertificateFileContent
        );

        $result = $this->service->loadCertificate($dummyDistinguishedName);
        $this->assertSame(var_export($dummyParsedCertificate, true), var_export($result, true));
    }
}
