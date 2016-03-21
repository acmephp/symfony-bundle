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
use AcmePhp\Core\LetsEncryptClient;

class BoulderConfiguration implements CertificateAuthorityConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'http://127.0.0.1:4000';
    }

    /**
     * {@inheritdoc}
     */
    public function getAgreement()
    {
        return 'http://127.0.0.1:4001/terms/v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getCertificatesChain()
    {
        return array_map('file_get_contents', LetsEncryptClient::getLetsEncryptCertificateChain());
    }

    /**
     * {@inheritdoc}
     */
    public function getChallengePath()
    {
        return '/.well-known/acme-challenge/{token}';
    }
}
