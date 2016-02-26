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

use AcmePhp\Core\Ssl\CSR;

/**
 * This class represent the configuration of a domain.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class DomainConfiguration
{
    /** @var string */
    private $domain;

    /** @var CSR */
    private $CSR;

    /**
     * @param string $domain
     * @param CSR    $CSR
     */
    public function __construct($domain, CSR $CSR)
    {
        $this->domain = $domain;
        $this->CSR = $CSR;
    }

    /**
     * Retrieves the domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Retrieves the Certificate Signed Request.
     *
     * @return CSR
     */
    public function getCSR()
    {
        return $this->CSR;
    }
}
