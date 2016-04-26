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
use AcmePhp\Ssl\KeyPair;
use AcmePhp\Ssl\PrivateKey;
use AcmePhp\Ssl\PublicKey;
use Symfony\Component\Filesystem\Filesystem;

class KeyPairStorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var KeyPairStorage */
    private $service;

    /** @var Filesystem */
    private $mockFilesystem;

    /** @var string */
    private $dummyStoragePath;

    public function setUp()
    {
        parent::setUp();

        $this->mockFilesystem = $this->prophesize(Filesystem::class);
        $this->dummyStoragePath = sys_get_temp_dir().'/'.uniqid();

        $this->service = new KeyPairStorage(
            $this->mockFilesystem->reveal(),
            $this->dummyStoragePath
        );
    }

    public function test exists asserts every file exists()
    {
        $dummySuccess = rand(0, 1) === 1;
        $this->mockFilesystem->exists(
            [
                $this->dummyStoragePath.'/public.pem',
                $this->dummyStoragePath.'/private.pem',
            ]
        )->willReturn($dummySuccess);

        $result = $this->service->exists();

        $this->assertSame($dummySuccess, $result);
    }

    public function test load dump each certificate()
    {
        $dummyPrivateKey = uniqid();
        $dummyPublicKey = uniqid();

        mkdir($this->dummyStoragePath);
        file_put_contents($this->dummyStoragePath.'/public.pem', $dummyPublicKey);
        file_put_contents($this->dummyStoragePath.'/private.pem', $dummyPrivateKey);
        try {
            $result = $this->service->load();
        } finally {
            unlink($this->dummyStoragePath.'/public.pem');
            unlink($this->dummyStoragePath.'/private.pem');
            rmdir($this->dummyStoragePath);
        }

        $this->assertInstanceOf(KeyPair::class, $result);
        $this->assertEquals($dummyPublicKey, $result->getPublicKey()->getPEM());
        $this->assertEquals($dummyPrivateKey, $result->getPrivateKey()->getPEM());
    }

    public function test store dump each certificate()
    {
        $dummyPrivateKey = uniqid();
        $dummyPublicKey = uniqid();

        $dummyKeyPair = new KeyPair(
            new PublicKey($dummyPublicKey),
            new PrivateKey($dummyPrivateKey)
        );

        $this->mockFilesystem->dumpFile($this->dummyStoragePath.'/public.pem', $dummyPublicKey)->shouldBeCalled();
        $this->mockFilesystem->dumpFile($this->dummyStoragePath.'/private.pem', $dummyPrivateKey)->shouldBeCalled();

        $this->service->store($dummyKeyPair);
    }

    public function test getRootPath returns the original storagePath()
    {
        $this->assertSame($this->dummyStoragePath, $this->service->getRootPath());
    }
}
