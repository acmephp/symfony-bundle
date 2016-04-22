<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\KeyPair\Storage;

use AcmePhp\Bundle\Acme\KeyPair\DomainKeyPairProviderFactory;
use AcmePhp\Bundle\Acme\KeyPair\KeyPairProvider;
use AcmePhp\Bundle\Acme\KeyPair\Storage\DomainKeyPairStorageFactory;
use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Ssl\Generator\KeyPairGenerator;
use Psr\Log\LoggerInterface;

class DomainKeyPairProviderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var DomainKeyPairProviderFactory */
    private $service;

    /** @var KeyPairManager */
    private $mockGenerator;

    /** @var DomainKeyPairStorageFactory */
    private $mockStorageFactory;

    public function setUp()
    {
        parent::setUp();

        $this->mockGenerator = $this->prophesize(KeyPairGenerator::class);
        $this->mockStorageFactory = $this->prophesize(DomainKeyPairStorageFactory::class);

        $this->service = new DomainKeyPairProviderFactory($this->mockGenerator->reveal(), $this->mockStorageFactory->reveal());
    }

    public function test createKeyPairProvider returns a new instance of KeyPairProvider()
    {
        $dummyDomain = uniqid();
        $dummyStorage = $this->prophesize(KeyPairStorage::class)->reveal();

        $this->mockStorageFactory->createKeyPairStorage($dummyDomain)->shouldBeCalled()->willReturn($dummyStorage);

        $provider = $this->service->createKeyPairProvider($dummyDomain);

        $this->assertInstanceOf(KeyPairProvider::class, $provider);
    }

    public function test createKeyPairProvider injects the logger()
    {
        $dummyDomain = uniqid();
        $dummyStorage = $this->prophesize(KeyPairStorage::class)->reveal();
        $dummyLogger = $this->prophesize(LoggerInterface::class)->reveal();

        $this->mockStorageFactory->createKeyPairStorage($dummyDomain)->shouldBeCalled()->willReturn($dummyStorage);
        $this->service->setLogger($dummyLogger);

        $provider = $this->service->createKeyPairProvider($dummyDomain);

        $reflection = new \ReflectionObject($provider);
        $loggerProperty = $reflection->getProperty('logger');
        $loggerProperty->setAccessible(true);

        $this->assertSame($dummyLogger, $loggerProperty->getValue($provider));
    }
}
