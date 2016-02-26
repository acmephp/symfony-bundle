<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Domain\Loader;

/**
 * A chain loader for DomainConfiguration.
 * Each registered loader will be call and merged with other domain's configurations.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class LoaderChain implements LoaderInterface
{
    /** @var LoaderInterface[] */
    private $loaders = [];

    /**
     * Add a new loader in the chain.
     *
     * @param LoaderInterface $loader
     */
    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $configurations = [];
        foreach ($this->loaders as $loader) {
            $configurations = array_merge($configurations, $loader->load());
        }

        return $configurations;
    }
}
