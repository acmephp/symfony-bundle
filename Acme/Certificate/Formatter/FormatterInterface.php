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
 * Format and merge certificates.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
interface FormatterInterface
{
    /**
     * Returns name of the formatter.
     *
     * @return string
     */
    public function getName();

    /**
     * Format the given certificate.
     *
     * @param Certificate $certificate
     * @param KeyPair     $domainKeyPair
     *
     * @return string
     */
    public function format(Certificate $certificate, KeyPair $domainKeyPair);
}
