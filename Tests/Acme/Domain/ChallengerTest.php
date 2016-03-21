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

use AcmePhp\Bundle\Acme\Domain\Challenger;
use AcmePhp\Bundle\Event\ChallengeEvent;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Protocol\Challenge;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChallengerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Challenger */
    private $service;

    /** @var AcmeClient */
    private $mockClient;

    /** @var EventDispatcherInterface */
    private $mockDispatcher;

    public function setUp()
    {
        parent::setUp();

        $this->mockClient = $this->prophesize(AcmeClient::class);
        $this->mockDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $this->service = new Challenger(
            $this->mockClient->reveal(),
            $this->mockDispatcher->reveal()
        );
    }

    public function test challengeDomain requests and checks the domain()
    {
        $dummyDomain = uniqid();
        $dummyChallenge = $this->prophesize(Challenge::class)->reveal();

        $this->mockClient->requestChallenge($dummyDomain)->shouldBeCalled()->willReturn($dummyChallenge);
        $this->mockClient->checkChallenge($dummyChallenge)->shouldBeCalled();

        $this->service->challengeDomain($dummyDomain);
    }

    public function test challengeDomain triggers events()
    {
        $dummyDomain = uniqid();
        $dummyChallenge = $this->prophesize(Challenge::class)->reveal();

        $this->mockClient->requestChallenge($dummyDomain)->shouldBeCalled()->willReturn($dummyChallenge);
        $this->mockClient->checkChallenge($dummyChallenge)->shouldBeCalled();

        $this->mockDispatcher->dispatch('acme_php.challenge.requested', Argument::that(function ($item) use ($dummyChallenge) {
            $this->assertInstanceOf(ChallengeEvent::class, $item);
            $this->assertSame($dummyChallenge, $item->getChallenge());

            return true;
        }))->shouldBeCalled();
        $this->mockDispatcher->dispatch('acme_php.challenge.accepted', Argument::that(function ($item) use ($dummyChallenge) {
            $this->assertInstanceOf(ChallengeEvent::class, $item);
            $this->assertSame($dummyChallenge, $item->getChallenge());

            return true;
        }))->shouldBeCalled();
        $this->mockDispatcher->dispatch('acme_php.challenge.checked', Argument::that(function ($item) use ($dummyChallenge) {
            $this->assertInstanceOf(ChallengeEvent::class, $item);
            $this->assertSame($dummyChallenge, $item->getChallenge());

            return true;
        }))->shouldBeCalled();

        $this->service->challengeDomain($dummyDomain);
    }

    /**
     * @expectedException \Exception
     */
    public function test challengeDomain triggers rejected event()
    {
        $dummyDomain = uniqid();
        $dummyChallenge = $this->prophesize(Challenge::class)->reveal();

        $this->mockClient->requestChallenge($dummyDomain)->shouldBeCalled()->willReturn($dummyChallenge);
        $this->mockClient->checkChallenge($dummyChallenge)->shouldBeCalled()->willThrow(new \Exception());

        $this->mockDispatcher->dispatch('acme_php.challenge.requested', Argument::that(function ($item) use ($dummyChallenge) {
            $this->assertInstanceOf(ChallengeEvent::class, $item);
            $this->assertSame($dummyChallenge, $item->getChallenge());

            return true;
        }))->shouldBeCalled();
        $this->mockDispatcher->dispatch('acme_php.challenge.accepted', Argument::any())->shouldNotBeCalled();
        $this->mockDispatcher->dispatch('acme_php.challenge.rejected', Argument::that(function ($item) use ($dummyChallenge) {
            $this->assertInstanceOf(ChallengeEvent::class, $item);
            $this->assertSame($dummyChallenge, $item->getChallenge());

            return true;
        }))->shouldBeCalled();
        $this->mockDispatcher->dispatch('acme_php.challenge.checked', Argument::that(function ($item) use ($dummyChallenge) {
            $this->assertInstanceOf(ChallengeEvent::class, $item);
            $this->assertSame($dummyChallenge, $item->getChallenge());

            return true;
        }))->shouldBeCalled();

        $this->service->challengeDomain($dummyDomain);
    }
}
