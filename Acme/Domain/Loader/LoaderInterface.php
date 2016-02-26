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

use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;

/**
 * A loader in charge of retrieving DomainConfigurations.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
interface LoaderInterface
{
    /**
     * Retrieve a list of DomainConfiguration.
     *
     * @return DomainConfiguration[]
     */
    public function load();
}
