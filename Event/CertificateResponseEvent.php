<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Event;

use AcmePhp\Ssl\CertificateResponse;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class for events thrown in the AcmePhp bundle related to certificateResponse.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateResponseEvent extends Event
{
    /** @var CertificateResponse */
    private $certificateResponse;

    /**
     * CertificateEvent constructor.
     *
     * @param CertificateResponse $certificate
     */
    public function __construct(CertificateResponse $certificate)
    {
        $this->certificateResponse = $certificate;
    }

    /**
     * @return CertificateResponse
     */
    public function getCertificateResponse()
    {
        return $this->certificateResponse;
    }
}
