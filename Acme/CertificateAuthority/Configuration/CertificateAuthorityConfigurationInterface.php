<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\CertificateAuthority\Configuration;

/**
 * Class representing the configuration of a Certificate Authority.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
interface CertificateAuthorityConfigurationInterface
{
    /**
     * Returns the API base URL.
     *
     * @return string
     */
    public function getDirectoryUri();

    /**
     * Return the agreement/license document URL.
     *
     * @return string
     */
    public function getAgreement();
}
