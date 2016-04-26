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

use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class for events thrown in the AcmePhp bundle related to certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ChallengeEvent extends Event
{
    /** @var AuthorizationChallenge */
    private $challenge;

    /**
     * ChallengeEvent constructor.
     *
     * @param AuthorizationChallenge $challenge
     */
    public function __construct(AuthorizationChallenge $challenge)
    {
        $this->challenge = $challenge;
    }

    /**
     * @return AuthorizationChallenge
     */
    public function getChallenge()
    {
        return $this->challenge;
    }
}
