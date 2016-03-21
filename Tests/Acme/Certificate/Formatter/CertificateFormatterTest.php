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

use AcmePhp\Bundle\Acme\Certificate\Formatter\CertificateFormatter;
use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;

class CertificateFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var CertificateFormatter */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = new CertificateFormatter();
    }

    public function test getName returns the name of a certificate file()
    {
        $this->assertSame('cert.pem', $this->service->getName());
    }

    public function test format returns the formatted certificate()
    {
        $dummyPem = uniqid();

        $mockCertificate = $this->prophesize(Certificate::class);
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $mockCertificate->getPem()->willReturn($dummyPem);

        $this->assertSame(
            $dummyPem,
            $this->service->format($mockCertificate->reveal(), $dummyKeyPair)
        );
    }
}
