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
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;

class CertificateStorageTest extends \PHPUnit_Framework_TestCase
{
    /** @var CertificateStorage */
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
        $this->dummyStoragePath = sys_get_temp_dir().'/'.uniqid();
        $this->dummyBackupStoragePath = sys_get_temp_dir().'/'.uniqid();

        $this->service = new CertificateStorage(
            $this->mockFilesystem->reveal(),
            $this->dummyStoragePath,
            $this->dummyBackupStoragePath
        );
    }

    public function test backup copy directory in backup storage path()
    {
        $this->mockFilesystem->mirror(
            $this->dummyStoragePath,
            Argument::containingString($this->dummyBackupStoragePath.'/')
        )->shouldBeCalled();

        $this->service->backup();
    }

    public function test hasCertificateFile asserts if the certificate file exists()
    {
        $dummyCertificateFile = uniqid();
        $dummySuccess = rand(0, 1) === 1;

        $this->mockFilesystem->exists($this->dummyStoragePath.'/'.$dummyCertificateFile)->willReturn($dummySuccess);

        $result = $this->service->hasCertificateFile($dummyCertificateFile);

        $this->assertSame($dummySuccess, $result);
    }

    /**
     * @expectedException \AcmePhp\Bundle\Exception\CertificateFileNotFoundException
     */
    public function test loadCertificateFile checks if the file exists()
    {
        $dummyCertificateFile = uniqid();

        $this->service->loadCertificateFile($dummyCertificateFile);
    }

    public function test loadCertificateFile returns content of the certificate file()
    {
        $dummyCertificateFile = uniqid();
        $dummyContent = uniqid();

        $this->mockFilesystem->exists($this->dummyStoragePath.'/'.$dummyCertificateFile)->willReturn(true);

        mkdir($this->dummyStoragePath);
        file_put_contents($this->dummyStoragePath.'/'.$dummyCertificateFile, $dummyContent);

        try {
            $result = $this->service->loadCertificateFile($dummyCertificateFile);
        } finally {
            unlink($this->dummyStoragePath.'/'.$dummyCertificateFile);
            rmdir($this->dummyStoragePath);
        }

        $this->assertSame($dummyContent, $result);
    }

    public function test removeCertificateFile removes the certificate file()
    {
        $dummyCertificateFile = uniqid();

        $this->mockFilesystem->remove($this->dummyStoragePath.'/'.$dummyCertificateFile)->shouldBeCalled();

        $this->service->removeCertificateFile($dummyCertificateFile);
    }

    public function test save dumps the content into the certificate file()
    {
        $dummyCertificateFile = uniqid();
        $dummyContent = uniqid();

        $this->mockFilesystem->dumpFile($this->dummyStoragePath.'/'.$dummyCertificateFile, $dummyContent)->shouldBeCalled();

        $this->service->saveCertificateFile($dummyCertificateFile, $dummyContent);
    }
}
