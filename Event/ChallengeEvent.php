<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Event;

use AcmePhp\Core\Protocol\Challenge;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class for events thrown in the AcmePhp bundle related to certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ChallengeEvent extends Event
{
    /** @var Challenge */
    private $challenge;

    /**
     * ChallengeEvent constructor.
     *
     * @param Challenge $challenge
     */
    public function __construct(Challenge $challenge)
    {
        $this->challenge = $challenge;
    }

    /**
     * @return Challenge
     */
    public function getChallenge()
    {
        return $this->challenge;
    }
}
