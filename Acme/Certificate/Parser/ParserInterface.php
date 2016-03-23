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

/**
 * Parse formatted certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
interface ParserInterface
{
    /**
     * Returns name of the related formatter.
     *
     * @return string
     */
    public function getName();

    /**
     * Parse raw certificate content to retrieve metadata.
     *
     * @param string $content
     *
     * @return CertificateMetadata
     */
    public function parse($content);
}
