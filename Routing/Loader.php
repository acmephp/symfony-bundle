<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Routing;

use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;

/**
 * A route loader decorator which add the CertificateAuthority challenge route.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class Loader implements LoaderInterface
{
    /** @var LoaderInterface */
    private $loader;

    /** @var CertificateAuthorityConfigurationInterface */
    private $certificateAuthority;

    /**
     * Router constructor.
     *
     * @param LoaderInterface $loader
     */
    public function __construct(LoaderInterface $loader, CertificateAuthorityConfigurationInterface $certificateAuthority)
    {
        $this->loader = $loader;
        $this->certificateAuthority = $certificateAuthority;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        $collection = $this->loader->load($resource, $type);
        $collection->add(
            'acme_php.challenge',
            new Route(
                $this->certificateAuthority->getChallengePath(),
                [
                    '_controller' => 'AcmePhp\\Bundle\\Controller\\ChallengeController::indexAction',
                ]
            )
        );

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return $this->loader->supports($resource, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
        return $this->loader->getResolver();
    }

    /**
     * {@inheritdoc}
     */
    public function setResolver(LoaderResolverInterface $resolver)
    {
        return $this->loader->setResolver($resolver);
    }
}
