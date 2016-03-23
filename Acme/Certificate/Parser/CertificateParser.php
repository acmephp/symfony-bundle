<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate\Parser;

use AcmePhp\Bundle\Acme\Certificate\CertificateMetadata;
use AcmePhp\Bundle\Exception\ParsingCertificateException;
use AcmePhp\Core\Ssl\Certificate;

/**
 * Parse certificate to extract metadata.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateParser
{
    /**
     * {@inheritdoc}
     */
    public function parse($content)
    {
        $rawData = openssl_x509_parse($content);
        if (!$rawData) {
            throw new ParsingCertificateException(
                sprintf('Certificate parsing failed with error: %s', openssl_error_string())
            );
        }

        return new CertificateMetadata(
            $rawData['subject']['CN'],
            $rawData['issuer']['CN'],
            false !== strpos(
                $rawData['extensions']['authorityKeyIdentifier'],
                $rawData['extensions']['subjectKeyIdentifier']
            ),
            $rawData['serialNumber'],
            array_map(
                function ($item) {
                    return explode(':', trim($item), 2)[1];
                },
                explode(',', $rawData['extensions']['subjectAltName'])
            )
        );
    }
}
