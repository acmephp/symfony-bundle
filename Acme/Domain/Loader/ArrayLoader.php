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
use AcmePhp\Core\Ssl\CSR;

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
        foreach ((array) $this->configurations as $domain => $domainConfiguration) {
            $configurations[] = new DomainConfiguration(
                $domain,
                new CSR(
                    $domainConfiguration['country'],
                    $domainConfiguration['state'],
                    $domainConfiguration['locality'],
                    $domainConfiguration['organization_name'],
                    $domainConfiguration['organization_unit_name'],
                    $domainConfiguration['email_address'],
                    (array) $domainConfiguration['subject_alternative_names']
                )
            );
        }

        return $configurations;
    }
}
