<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * AppKernel for tests.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new TestAppBundle\TestAppBundle(),
            new AcmePhp\Bundle\AcmePhpBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }

    private $containerNameCounter = 0;

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        ++$this->containerNameCounter;

        return parent::boot();
    }

    protected function getContainerClass()
    {
        return $this->name.ucfirst(
            $this->environment
        ).($this->debug ? 'Debug' : '').'ProjectContainer'.$this->containerNameCounter;
    }
}
