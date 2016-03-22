<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Controller;

use AcmePhp\Bundle\Exception\ChallengeNotFoundException;
use AcmePhp\Core\Protocol\Challenge;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handle challenge requests from CA.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ChallengeController extends Controller
{
    /**
     * Main action.
     *
     * @param $token
     *
     * @return Response
     */
    public function checkAction($token)
    {
        $logger = $this->get('acme_php.logger');
        try {
            /** @var Challenge $challenge */
            $challenge = $this->get('acme_php.challenge.repository')->findOneByToken($token);
        } catch (ChallengeNotFoundException $e) {
            $logger->warn(
                'Challenge not found. "{token}" was expected.',
                [
                    'token' => $token,
                ]
            );

            throw $this->createNotFoundException();
        }
        $logger->debug(
            'Reply to CA for token "{token}" with "{payload}"',
            [
                'payload' => $challenge->getPayload(),
                'token' => $challenge->getToken(),
            ]
        );

        return new Response($challenge->getPayload());
    }
}
