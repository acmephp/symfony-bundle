<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\EventListener;

use AcmePhp\Bundle\Acme\Domain\ChallengeRepository;
use AcmePhp\Bundle\Event\ChallengeEvent;
use AcmePhp\Bundle\Event\AcmePhpBundleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to challenge requests to persists payload.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ChallengePersisterListener implements EventSubscriberInterface
{
    /** @var ChallengeRepository */
    private $repository;

    /**
     * @param ChallengeRepository $repository
     */
    public function __construct(ChallengeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AcmePhpBundleEvents::CHALLENGE_REQUESTED => 'onChallengeRequested',
            AcmePhpBundleEvents::CHALLENGE_CHECKED => 'onChallengeChecked',
        ];
    }

    /**
     * Triggered when a challenge is requested.
     *
     * @param ChallengeEvent $event
     */
    public function onChallengeRequested(ChallengeEvent $event)
    {
        $this->repository->persistChallenge($event->getChallenge());
    }

    /**
     * Triggered when a challenge is checked.
     *
     * @param ChallengeEvent $event
     */
    public function onChallengeChecked(ChallengeEvent $event)
    {
        $this->repository->removeChallenge($event->getChallenge());
    }
}
