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

use AcmePhp\Ssl\DistinguishedName;

/**
 * Load domainConfigurations from the Symfony's config files.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ArrayLoader implements LoaderInterface
{
    /** @var array */
    private $configurations;

    /**
     * @param array $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $configurations = [];
        foreach ((array) $this->configurations as $commonName => $domainConfiguration) {
            $maskConfiguration = array_replace(
                array_fill_keys(
                    ['country', 'state', 'locality', 'organization_name', 'organization_unit_name', 'email_address'],
                    null
                ),
                $domainConfiguration
            );

            $configurations[] = new DistinguishedName(
                $commonName,
                $maskConfiguration['country'] ?: null,
                $maskConfiguration['state'],
                $maskConfiguration['locality'],
                $maskConfiguration['organization_name'],
                $maskConfiguration['organization_unit_name'],
                $maskConfiguration['email_address'],
                (array) $maskConfiguration['subject_alternative_names']
            );
        }

        return $configurations;
    }
}
