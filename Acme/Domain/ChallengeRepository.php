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

use AcmePhp\Bundle\Exception\ChallengeNotFoundException;
use AcmePhp\Core\Protocol\AuthorizationChallenge;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Store and load challenges in a repository.
 */
class ChallengeRepository
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $storagePath;

    /**
     * @param Filesystem $filesystem
     * @param string     $storagePath
     */
    public function __construct(Filesystem $filesystem, $storagePath)
    {
        $this->filesystem = $filesystem;
        $this->storagePath = $storagePath;
    }

    /**
     * Persists the given challenge in the repository.
     *
     * @param AuthorizationChallenge $challenge
     */
    public function persistChallenge(AuthorizationChallenge $challenge)
    {
        $this->filesystem->dumpFile(
            $this->getFilePath($challenge->getToken()),
            serialize($challenge)
        );
    }

    /**
     * Retrieves a challenge by it token.
     *
     * @param string $token
     *
     * @return AuthorizationChallenge
     */
    public function findOneByToken($token)
    {
        $filePath = $this->getFilePath($token);
        if (!$this->filesystem->exists($filePath)) {
            throw new ChallengeNotFoundException(sprintf('The challenge "%s" does not exists', $token));
        }

        return unserialize(file_get_contents($filePath));
    }

    /**
     * Remove the given challenge.
     *
     * @param AuthorizationChallenge $challenge
     */
    public function removeChallenge(AuthorizationChallenge $challenge)
    {
        $filePath = $this->getFilePath($challenge->getToken());
        if (!$this->filesystem->exists($filePath)) {
            throw new ChallengeNotFoundException(sprintf('The challenge "%s" does not exists', $challenge->getToken()));
        }

        $this->filesystem->remove($filePath);
    }

    /**
     * Retrieves path to the chalenge's file.
     *
     * @param string $token
     *
     * @return string
     */
    private function getFilePath($token)
    {
        return $this->storagePath.DIRECTORY_SEPARATOR.$token;
    }
}
