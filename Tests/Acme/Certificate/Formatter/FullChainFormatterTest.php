<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\Certificate\Formatter;

use AcmePhp\Bundle\Acme\Certificate\Formatter\FullChainFormatter;
use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;

class FullChainFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var FullChainFormatter */
    private $service;

    /** @var CertificateAuthorityConfigurationInterface */
    private $mockCertificateAuthorityConfiguration;

    public function setUp()
    {
        parent::setUp();

        $this->mockCertificateAuthorityConfiguration = $this->prophesize(CertificateAuthorityConfigurationInterface::class);

        $this->service = new FullChainFormatter($this->mockCertificateAuthorityConfiguration->reveal());
    }

    public function test getName returns the name of a certificate file()
    {
        $this->assertSame('fullchain.pem', $this->service->getName());
    }

    public function test format returns the formatted certificate()
    {
        $dummyCertificatePem = uniqid();
        $dummyChainPem1 = uniqid();
        $dummyChainPem2 = uniqid();

        $mockCertificate = $this->prophesize(Certificate::class);
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $mockCertificate->getPem()->willReturn($dummyCertificatePem);
        $this->mockCertificateAuthorityConfiguration->getCertificatesChain()->willReturn([$dummyChainPem1, $dummyChainPem2]);

        $this->assertSame(
            $dummyCertificatePem.$dummyChainPem1.$dummyChainPem2,
            $this->service->format($mockCertificate->reveal(), $dummyKeyPair)
        );
    }
}
