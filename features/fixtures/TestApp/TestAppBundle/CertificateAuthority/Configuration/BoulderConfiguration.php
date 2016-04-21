<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TestAppBundle\CertificateAuthority\Configuration;

use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;

class BoulderConfiguration implements CertificateAuthorityConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDirectoryUri()
    {
        return 'http://127.0.0.1:4000/directory';
    }

    /**
     * {@inheritdoc}
     */
    public function getAgreement()
    {
        return 'http://boulder:4000/terms/v1';
    }
}
