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

use AcmePhp\Bundle\Acme\KeyPair\KeyPairProvider;
use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Core\Ssl\KeyPair;
use AcmePhp\Core\Ssl\KeyPairManager;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class KeyPairProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var KeyPairProvider */
    private $service;

    /** @var KeyPairManager */
    private $mockManager;

    /** @var KeyPairStorage */
    private $mockStorage;

    /** @var LoggerInterface */
    private $mockLogger;

    public function setUp()
    {
        parent::setUp();

        $this->mockLogger = $this->prophesize(LoggerInterface::class);
        $this->mockManager = $this->prophesize(KeyPairManager::class);
        $this->mockStorage = $this->prophesize(KeyPairStorage::class);
        $this->mockStorage->getRootPath()->willReturn('~/.acme/certificates');

        $this->service = new KeyPairProvider($this->mockManager->reveal(), $this->mockStorage->reveal());
        $this->service->setLogger($this->mockLogger->reveal());
    }

    public function test hasKeyPair returns whether or not the KeyPair exists in the storage()
    {
        $dummyExists = (bool) rand(0, 1);

        $this->mockStorage->exists()->shouldBeCalled()->willReturn($dummyExists);

        $response = $this->service->hasKeyPair();

        $this->assertSame($dummyExists, $response);
    }

    public function test getKeyPair returns the stored KeyPair()
    {
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockStorage->load()->shouldBeCalled()->willReturn($dummyKeyPair);

        $response = $this->service->getKeyPair();

        $this->assertSame($dummyKeyPair, $response);
    }

    public function test createKeyPair generates a new KeyPair and store it()
    {
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockManager->generateKeyPair()->shouldBeCalled()->willReturn($dummyKeyPair);
        $this->mockStorage->store($dummyKeyPair)->shouldBeCalled();

        $this->service->createKeyPair();
    }

    public function test createKeyPair will notice the logger()
    {
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockLogger->info(Argument::containingString('Generating new KeyPair'), Argument::any())->shouldBeCalled();

        $this->mockManager->generateKeyPair()->shouldBeCalled()->willReturn($dummyKeyPair);
        $this->mockStorage->store($dummyKeyPair)->shouldBeCalled();

        $this->service->createKeyPair();
    }

    public function test getOrCreateKeyPair creates a KeyPair when it does not exists()
    {
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockStorage->exists()->willReturn(false);

        $this->mockManager->generateKeyPair()->shouldBeCalled()->willReturn($dummyKeyPair);
        $this->mockStorage->store($dummyKeyPair)->shouldBeCalled();

        $this->mockStorage->load()->shouldBeCalled()->willReturn($dummyKeyPair);

        $this->service->getOrCreateKeyPair();
    }

    public function test getOrCreateKeyPair does not creates a KeyPair when it exists()
    {
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockStorage->exists()->willReturn(true);

        $this->mockManager->generateKeyPair()->shouldNotBeCalled();

        $this->mockStorage->load()->shouldBeCalled()->willReturn($dummyKeyPair);

        $this->service->getOrCreateKeyPair();
    }

    public function test storeKeyPair stores the given keyPair()
    {
        $dummyKeyPair = $this->prophesize(KeyPair::class)->reveal();

        $this->mockStorage->store($dummyKeyPair)->shouldBeCalled();

        $this->service->storeKeyPair($dummyKeyPair);
    }
}
