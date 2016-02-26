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

use AcmePhp\Core\LetsEncryptClient;

/**
 * Class representing the configuration of the LetsEncrypt Certificate Authority.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class LetsEncryptConfiguration implements CertificateAuthorityConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBaseUri()
    {
        return 'https://acme-v01.api.letsencrypt.org';
    }

    /**
     * {@inheritdoc}
     */
    public function getAgreement()
    {
        return 'https://letsencrypt.org/documents/LE-SA-v1.0.1-July-27-2015.pdf';
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
