<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\Certificate\Storage;

use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorage;
use AcmePhp\Bundle\Acme\Certificate\Storage\CertificateStorageFactory;
use Symfony\Component\Filesystem\Filesystem;

class CertificateStorageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var CertificateStorageFactory */
    private $service;

    /** @var Filesystem */
    private $mockFilesystem;

    /** @var string */
    private $dummyStoragePath;

    /** @var string */
    private $dummyBackupStoragePath;

    public function setUp()
    {
        parent::setUp();

        $this->mockFilesystem = $this->prophesize(Filesystem::class);
        $this->dummyStoragePath = uniqid();
        $this->dummyBackupStoragePath = uniqid();

        $this->service = new CertificateStorageFactory(
            $this->mockFilesystem->reveal(),
            $this->dummyStoragePath,
            $this->dummyBackupStoragePath
        );
    }

    public function test createCertificateStorage returns an instance of CertificateStorage()
    {
        $domain = uniqid();

        $result = $this->service->createCertificateStorage($domain);

        $this->assertInstanceOf(CertificateStorage::class, $result);

        $reflection = new \ReflectionObject($result);
        $storagePathProperty = $reflection->getProperty('storagePath');
        $storagePathProperty->setAccessible(true);

        $this->assertSame($this->dummyStoragePath.'/'.$domain, $storagePathProperty->getValue($result));
    }
}
