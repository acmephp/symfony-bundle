<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\EventListener;

use AcmePhp\Bundle\Acme\Certificate\CertificateRepository;
use AcmePhp\Bundle\Event\CertificateResponseEvent;
use AcmePhp\Bundle\EventListener\CertificatePersisterListener;
use AcmePhp\Ssl\Certificate;
use AcmePhp\Ssl\CertificateRequest;
use AcmePhp\Ssl\CertificateResponse;
use AcmePhp\Ssl\DistinguishedName;
use AcmePhp\Ssl\KeyPair;

class CertificatePersisterListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CertificatePersisterListener */
    private $service;

    /** @var CertificateRepository */
    private $mockRepository;

    public function setUp()
    {
        parent::setUp();

        $this->mockRepository = $this->prophesize(CertificateRepository::class);

        $this->service = new CertificatePersisterListener(
            $this->mockRepository->reveal()
        );
    }

    public function test getSubscribedEvents listens to certificate request()
    {
        $result = $this->service->getSubscribedEvents();
        $this->assertArrayHasKey('acme_php.certificate.requested', $result);
    }

    public function test onCertificateRequested persists the certificate()
    {
        $dummyDistinguishedName = $this->prophesize(DistinguishedName::class)->reveal();
        $dummyCertificateResponse = new CertificateResponse(
            new CertificateRequest(
                $dummyDistinguishedName,
                $this->prophesize(KeyPair::class)->reveal()
            ),
            $this->prophesize(Certificate::class)->reveal()
        );

        $event = new CertificateResponseEvent($dummyCertificateResponse);

        $this->mockRepository->persistCertificate($dummyDistinguishedName, $dummyCertificateResponse)->shouldBeCalled();

        $this->service->onCertificateRequested($event);
    }
}
