<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Acme\Certificate\Extractor;

use AcmePhp\Core\Ssl\Certificate;

/**
 * Extract data from formatted certificate.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
interface ExtractorInterface
{
    /**
     * Returns name of the related formatter.
     *
     * @return string
     */
    public function getName();

    /**
     * Extract data.
     *
     * @param string $content
     */
    public function extract($content);
}
