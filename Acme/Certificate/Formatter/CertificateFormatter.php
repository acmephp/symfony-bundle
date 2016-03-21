<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate\Formatter;

use AcmePhp\Core\Ssl\Certificate;
use AcmePhp\Core\Ssl\KeyPair;

/**
 * Format the base certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cert.pem';
    }

    /**
     * {@inheritdoc}
     */
    public function format(Certificate $certificate, KeyPair $domainKeyPair)
    {
        return $certificate->getPem();
    }
}
