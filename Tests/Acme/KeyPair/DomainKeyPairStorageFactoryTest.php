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

use AcmePhp\Bundle\Acme\KeyPair\Storage\DomainKeyPairStorageFactory;
use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Core\Ssl\KeyPairManager;
use Symfony\Component\Filesystem\Filesystem;

class DomainKeyPairStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var DomainKeyPairStorageFactory */
    private $service;

    /** @var Filesystem */
    private $mockFilesystem;

    /** @var KeyPairManager */
    private $mockKeyPairManager;

    /** @var string */
    private $dummyStoragePath;

    public function setUp()
    {
        parent::setUp();

        $this->mockFilesystem = $this->prophesize(Filesystem::class);
        $this->mockKeyPairManager = $this->prophesize(KeyPairManager::class);
        $this->dummyStoragePath = uniqid();

        $this->service = new DomainKeyPairStorageFactory(
            $this->mockFilesystem->reveal(),
            $this->mockKeyPairManager->reveal(),
            $this->dummyStoragePath
        );
    }

    public function test createKeyPairStorage returns an instance of CertificateStorage()
    {
        $domain = uniqid();

        $result = $this->service->createKeyPairStorage($domain);

        $this->assertInstanceOf(KeyPairStorage::class, $result);

        $reflection = new \ReflectionObject($result);
        $storagePathProperty = $reflection->getProperty('storagePath');
        $storagePathProperty->setAccessible(true);

        $this->assertSame($this->dummyStoragePath.'/'.$domain, $storagePathProperty->getValue($result));
    }
}
