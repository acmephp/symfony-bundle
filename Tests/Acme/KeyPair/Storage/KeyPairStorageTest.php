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

use AcmePhp\Bundle\Acme\KeyPair\Storage\KeyPairStorage;
use AcmePhp\Core\Ssl\KeyPair;
use AcmePhp\Core\Ssl\KeyPairManager;
use Symfony\Component\Filesystem\Filesystem;

class KeyPairStorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var KeyPairStorage */
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

        $this->service = new KeyPairStorage(
            $this->mockFilesystem->reveal(),
            $this->mockKeyPairManager->reveal(),
            $this->dummyStoragePath
        );
    }

    public function test exists asserts every file exists()
    {
        $dummySuccess = rand(0, 1) === 1;
        $this->mockFilesystem->exists([
            $this->dummyStoragePath.'/public.pem',
            $this->dummyStoragePath.'/private.pem',
        ])->willReturn($dummySuccess);

        $result = $this->service->exists();

        $this->assertSame($dummySuccess, $result);
    }

    public function test store dump each certificate()
    {
        $dummyPrivateKey = uniqid();
        $dummyPublicKey = uniqid();

        $mockKeyPair = $this->prophesize(KeyPair::class);

        $mockKeyPair->getPublicKeyAsPEM()->shouldBeCalled()->willReturn($dummyPublicKey);
        $mockKeyPair->getPrivateKeyAsPEM()->shouldBeCalled()->willReturn($dummyPrivateKey);

        $this->mockFilesystem->dumpFile($this->dummyStoragePath.'/public.pem', $dummyPublicKey)->shouldBeCalled();
        $this->mockFilesystem->dumpFile($this->dummyStoragePath.'/private.pem', $dummyPrivateKey)->shouldBeCalled();

        $this->service->store($mockKeyPair->reveal());
    }

    public function test load use the KeyPairManager to load the stored certificates()
    {
        $dummyKeyPair = uniqid();

        $this->mockKeyPairManager->loadKeyPair(
            $this->dummyStoragePath.'/public.pem',
            $this->dummyStoragePath.'/private.pem'
        )->shouldBeCalled()->willReturn($dummyKeyPair);

        $result = $this->service->load();

        $this->assertSame($dummyKeyPair, $result);
    }

    public function test getRootPath returns the original storagePath()
    {
        $this->assertSame($this->dummyStoragePath, $this->service->getRootPath());
    }
}
