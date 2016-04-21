<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Domain;

use AcmePhp\Bundle\Event\ChallengeEvent;
use AcmePhp\Bundle\Event\AcmePhpBundleEvents;
use AcmePhp\Core\AcmeClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

/**
 * Domain challenger.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class Challenger
{
    /** @var AcmeClient */
    protected $client;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param AcmeClient               $client
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(AcmeClient $client, EventDispatcherInterface $dispatcher)
    {
        $this->client = $client;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Request and check a list of domain.
     *
     * @param array $domains
     *
     * @throws \Exception
     */
    public function challengeDomains(array $domains)
    {
        Assert::allStringNotEmpty(
            $domains,
             'challengeDomains::$domains expected an array of non-empty string. Got: %s'
        );

        foreach (array_unique($domains) as $domain) {
            $this->challengeDomain($domain);
        }
    }

    /**
     * Request and check a domain.
     *
     * @param string $domain
     *
     * @throws \Exception
     */
    public function challengeDomain($domain)
    {
        Assert::stringNotEmpty($domain, 'challengeDomain::$domain expected a non-empty string. Got: %s');

        $challenge = $this->client->requestChallenge($domain);
        $challengeEvent = new ChallengeEvent($challenge);
        $this->dispatcher->dispatch(AcmePhpBundleEvents::CHALLENGE_REQUESTED, $challengeEvent);

        try {
            $this->client->checkChallenge($challenge);
            $this->dispatcher->dispatch(AcmePhpBundleEvents::CHALLENGE_ACCEPTED, $challengeEvent);
        } catch (\Exception $e) {
            $this->dispatcher->dispatch(AcmePhpBundleEvents::CHALLENGE_REJECTED, $challengeEvent);
            throw $e;
        } finally {
            $this->dispatcher->dispatch(AcmePhpBundleEvents::CHALLENGE_CHECKED, $challengeEvent);
        }
    }
}
