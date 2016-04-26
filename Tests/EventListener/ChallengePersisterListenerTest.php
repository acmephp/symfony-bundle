<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\EventListener;

use AcmePhp\Bundle\Acme\Domain\ChallengeRepository;
use AcmePhp\Bundle\Event\ChallengeEvent;
use AcmePhp\Bundle\EventListener\ChallengePersisterListener;
use AcmePhp\Core\Protocol\AuthorizationChallenge;

class ChallengePersisterListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChallengePersisterListener */
    private $service;

    /** @var ChallengeRepository */
    private $mockRepository;

    public function setUp()
    {
        parent::setUp();

        $this->mockRepository = $this->prophesize(ChallengeRepository::class);

        $this->service = new ChallengePersisterListener(
            $this->mockRepository->reveal()
        );
    }

    public function test getSubscribedEvents listens to challenge events()
    {
        $result = $this->service->getSubscribedEvents();
        $this->assertArrayHasKey('acme_php.challenge.requested', $result);
        $this->assertArrayHasKey('acme_php.challenge.checked', $result);
    }

    public function test onChallengeRequested persists the challenge()
    {
        $dummyChallenge = $this->prophesize(AuthorizationChallenge::class)->reveal();

        $event = new ChallengeEvent($dummyChallenge);

        $this->mockRepository->persistChallenge($dummyChallenge)->shouldBeCalled();

        $this->service->onChallengeRequested($event);
    }

    public function test onChallengeChecked remove the stored challenge()
    {
        $dummyChallenge = $this->prophesize(AuthorizationChallenge::class)->reveal();

        $event = new ChallengeEvent($dummyChallenge);

        $this->mockRepository->removeChallenge($dummyChallenge)->shouldBeCalled();

        $this->service->onChallengeChecked($event);
    }
}
