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

use AcmePhp\Bundle\Acme\Certificate\Formatter\ChainFormatter;
use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;

class ChainFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChainFormatter */
    private $service;

    /** @var CertificateAuthorityConfigurationInterface */
    private $mockCertificateAuthorityConfiguration;

    public function setUp()
    {
        parent::setUp();

        $this->mockCertificateAuthorityConfiguration = $this->prophesize(CertificateAuthorityConfigurationInterface::class);

        $this->service = new ChainFormatter($this->mockCertificateAuthorityConfiguration->reveal());
    }

    public function test getName returns the name of a certificate file()
    {
        $this->assertSame('chain.pem', $this->service->getName());
    }

    public function test format returns the formatted certificate()
    {
        $dummyChainPem1 = uniqid();
        $dummyChainPem2 = uniqid();

        $dummyCertificate = $this->prophesize(Certificate::class)->reveal();
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockCertificateAuthorityConfiguration->getCertificatesChain()->willReturn([$dummyChainPem1, $dummyChainPem2]);

        $this->assertSame(
            $dummyChainPem1.$dummyChainPem2,
            $this->service->format($dummyCertificate, $dummyKeyPair)
        );
    }
}
