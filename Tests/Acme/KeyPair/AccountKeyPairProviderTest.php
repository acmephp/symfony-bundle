<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\KeyPair;

use AcmePhp\Bundle\Acme\CertificateAuthority\ClientFactory;
use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Bundle\Acme\KeyPair\AccountKeyPairProvider;
use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use AcmePhp\Ssl\KeyPair;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class AccountKeyPairProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var AccountKeyPairProvider */
    private $service;

    /** @var KeyPairManager */
    private $mockGenerator;

    /** @var KeyPairStorage */
    private $mockStorage;

    /** @var ClientFactory */
    private $mockClientFactory;

    /** @var CertificateAuthorityConfigurationInterface */
    private $mockCA;

    /** @var string */
    private $dummyContactEmail;

    public function setUp()
    {
        parent::setUp();

        $this->mockGenerator = $this->prophesize(KeyPairGenerator::class);
        $this->mockStorage = $this->prophesize(KeyPairStorage::class);
        $this->mockClientFactory = $this->prophesize(ClientFactory::class);
        $this->mockCA = $this->prophesize(CertificateAuthorityConfigurationInterface::class);
        $this->dummyContactEmail = sprintf('%s@company.com', uniqid());

        $this->service = new AccountKeyPairProvider(
            $this->mockGenerator->reveal(),
            $this->mockStorage->reveal(),
            $this->mockClientFactory->reveal(),
            $this->mockCA->reveal(),
            $this->dummyContactEmail
        );
    }

    public function test register create an acme client to register the account()
    {
        $dummyAgreement = uniqid();
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockCA->getAgreement()->willReturn($dummyAgreement);
        $mockClient = $this->prophesize(AcmeClient::class);
        $mockClient->registerAccount($dummyAgreement, $this->dummyContactEmail)->shouldBeCalled();

        $this->mockClientFactory->createAcmeClient($dummyKeyPair)->shouldBeCalled()->willReturn($mockClient->reveal());

        $this->service->register($dummyKeyPair);
    }

    public function test register will notice the logger()
    {
        $dummyAgreement = uniqid();
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockCA->getAgreement()->willReturn($dummyAgreement);
        $mockClient = $this->prophesize(AcmeClient::class);
        $mockClient->registerAccount($dummyAgreement, $this->dummyContactEmail)->shouldBeCalled();

        $mockLogger = $this->prophesize(LoggerInterface::class);
        $this->service->setLogger($mockLogger->reveal());
        $mockLogger->notice(
            Argument::containingString('Account {contactEmail} registered'),
            ['contactEmail' => $this->dummyContactEmail]
        )->shouldBeCalled();

        $this->mockClientFactory->createAcmeClient($dummyKeyPair)->shouldBeCalled()->willReturn($mockClient->reveal());

        $this->service->register($dummyKeyPair);
    }

    public function test storeKeyPair register the client then store the keypair()
    {
        $dummyAgreement = uniqid();
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockCA->getAgreement()->willReturn($dummyAgreement);
        $mockClient = $this->prophesize(AcmeClient::class);
        $mockClient->registerAccount($dummyAgreement, $this->dummyContactEmail)->shouldBeCalled();

        $this->mockClientFactory->createAcmeClient($dummyKeyPair)->shouldBeCalled()->willReturn($mockClient->reveal());

        $this->mockStorage->store($dummyKeyPair)->shouldBeCalled();

        $this->service->storeKeyPair($dummyKeyPair);
    }
}
