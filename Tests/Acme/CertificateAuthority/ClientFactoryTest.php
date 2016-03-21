<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\CertificateAuthority;

use AcmePhp\Bundle\Acme\CertificateAuthority\ClientFactory;
use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Ssl\KeyPair;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClientFactory */
    private $service;

    /** @var CertificateAuthorityConfigurationInterface */
    private $mockCA;

    public function setUp()
    {
        parent::setUp();

        $this->mockCA = $this->prophesize(CertificateAuthorityConfigurationInterface::class);

        $this->service = new ClientFactory($this->mockCA->reveal());
    }

    public function test createAcmeClient use CA configuration()
    {
        $keyPair = $this->prophesize(KeyPair::class)->reveal();
        $this->mockCA->getBaseUri()->shouldBeCalled();
        $this->mockCA->getAgreement()->shouldBeCalled();

        $client = $this->service->createAcmeClient($keyPair);

        $this->assertInstanceOf(AcmeClient::class, $client);
    }
}
