<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\CertificateAuthority\Configuration;

use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\LetsEncryptConfiguration;

class LetsEncryptConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /** @var LetsEncryptConfiguration */
    private $service;

    public function __construct()
    {
        $this->service = new LetsEncryptConfiguration();
    }

    public function test getBaseUri returns the official letsencrypt api uri()
    {
        $url = parse_url($this->service->getBaseUri());

        $this->assertSame('https', $url['scheme']);
        $this->assertContains('api.letsencrypt.org', $url['host']);
    }

    public function test getAgreement returns the official letsencrypt licence uri()
    {
        $url = parse_url($this->service->getAgreement());

        $this->assertSame('https', $url['scheme']);
        $this->assertContains('letsencrypt.org', $url['host']);
        $this->assertContains('.pdf', $url['path']);
    }

    public function test getCertificatesChain returns a list of letsencrypt certificates()
    {
        $certificates = $this->service->getCertificatesChain();

        $this->assertInternalType('array', $certificates);
        $this->assertCount(2, $certificates);
    }

    public function test getChallengePath returns a path to the letsencrypt challenge()
    {
        $path = $this->service->getChallengePath();

        $this->assertContains('{token}', $path);
        $this->assertContains('.well-known/acme-challenge', $path);
    }
}
