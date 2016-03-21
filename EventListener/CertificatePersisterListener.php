<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\EventListener;

use AcmePhp\Bundle\Acme\Certificate\CertificateRepository;
use AcmePhp\Bundle\Event\CertificateEvent;
use AcmePhp\Bundle\Event\AcmePhpBundleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to certificate generation and persist it.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificatePersisterListener implements EventSubscriberInterface
{
    /** @var CertificateRepository */
    private $certificateRepository;

    public function __construct(CertificateRepository $certificateRepository)
    {
        $this->certificateRepository = $certificateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AcmePhpBundleEvents::CERTIFICATE_REQUESTED => 'onCertificateRequested',
        ];
    }

    /**
     * Triggered when a certificate is requested.
     *
     * @param CertificateEvent $event
     */
    public function onCertificateRequested(CertificateEvent $event)
    {
        $this->certificateRepository->persistCertificate(
            $event->getDomainConfiguration(),
            $event->getCertificate(),
            $event->getDomainKeyPair()
        );
    }
}
