<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\Domain;

use AcmePhp\Bundle\Acme\Domain\ChallengeRepository;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Symfony\Component\Filesystem\Filesystem;

class ChallengeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChallengeRepository */
    private $service;

    /** @var Filesystem */
    private $mockFilesystem;

    /** @var string */
    private $dummyStoragePath;

    public function setUp()
    {
        parent::setUp();

        $this->mockFilesystem = $this->prophesize(Filesystem::class);
        $this->dummyStoragePath = uniqid();

        $this->service = new ChallengeRepository(
            $this->mockFilesystem->reveal(),
            $this->dummyStoragePath
        );
    }

    public function test persistChallenge dumps a serialized challenge()
    {
        $dummyToken = uniqid();

        $mockChallenge = $this->prophesize(AuthorizationChallenge::class);
        $mockChallenge->getToken()->willReturn($dummyToken);

        $this->mockFilesystem->dumpFile(
            $this->dummyStoragePath.'/'.$dummyToken,
            serialize($mockChallenge)
        );

        $this->service->persistChallenge($mockChallenge->reveal());
    }

    /**
     * @expectedException \AcmePhp\Bundle\Exception\ChallengeNotFoundException
     */
    public function test findOneByToken raise exception if file does not exists()
    {
        $dummyToken = uniqid();

        $this->mockFilesystem->exists($this->dummyStoragePath.'/'.$dummyToken)->willReturn(false);
        $this->service->findOneByToken($dummyToken);
    }

    public function test findOneByToken returns the dumps challenge deserialized()
    {
        $dummyToken = uniqid();
        $dummyChallenge = uniqid();

        $this->mockFilesystem->exists($this->dummyStoragePath.'/'.$dummyToken)->willReturn(true);

        mkdir($this->dummyStoragePath);
        file_put_contents($this->dummyStoragePath.'/'.$dummyToken, serialize($dummyChallenge));

        try {
            $result = $this->service->findOneByToken($dummyToken);
        } finally {
            unlink($this->dummyStoragePath.'/'.$dummyToken);
            rmdir($this->dummyStoragePath);
        }

        $this->assertSame($dummyChallenge, $result);
    }

    /**
     * @expectedException \AcmePhp\Bundle\Exception\ChallengeNotFoundException
     */
    public function test removeChallenge raise exception if file does not exists()
    {
        $dummyToken = uniqid();
        $mockChallenge = $this->prophesize(AuthorizationChallenge::class);
        $mockChallenge->getToken()->willReturn($dummyToken);

        $this->mockFilesystem->exists($this->dummyStoragePath.'/'.$dummyToken)->willReturn(false);
        $this->service->removeChallenge($mockChallenge->reveal());
    }

    public function test removeChallenge removes the challenge file()
    {
        $dummyToken = uniqid();
        $mockChallenge = $this->prophesize(AuthorizationChallenge::class);
        $mockChallenge->getToken()->willReturn($dummyToken);

        $this->mockFilesystem->exists($this->dummyStoragePath.'/'.$dummyToken)->willReturn(true);
        $this->mockFilesystem->remove($this->dummyStoragePath.'/'.$dummyToken)->shouldBeCalled();

        $this->service->removeChallenge($mockChallenge->reveal());
    }
}
