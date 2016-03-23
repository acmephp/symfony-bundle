<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

/**
 * Defines application features from the specific context.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    /** @var string */
    private $storageDir;

    /** @var string */
    private $acmeConfigPath;

    /** @var Filesystem */
    private $filesystem;

    /** @var Kernel */
    private $kernel;

    public function __construct($acmeConfigPath, Filesystem $filesystem, Kernel $kernel)
    {
        $this->storageDir = $kernel->getContainer()->getParameter('acme_php.certificate_dir');
        $this->acmeConfigPath = $kernel->getContainer()->getParameter('kernel.root_dir').'/'.$acmeConfigPath;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario
     */
    public function initStorage()
    {
        $this->dropStorage();

        $this->filesystem->mkdir($this->storageDir);
    }

    /**
     * @AfterScenario
     */
    public function dropStorage()
    {
        $this->filesystem->remove($this->storageDir);
    }

    /**
     * @Given the following acme_php configuration:
     */
    public function theFollowingAcmePhpConfiguration(PyStringNode $rawConfig)
    {
        $yaml = new Yaml();

        $config = [
            'acme_php' => array_merge(
                $this->getDefaultConfig(),
                $yaml->parse($rawConfig->getRaw())
            ),
        ];

        $this->filesystem->dumpFile($this->acmeConfigPath, $yaml->dump($config, 4));
        $this->kernel->shutdown();
        $this->filesystem->remove($this->kernel->getCacheDir());
        $this->kernel->boot();
    }

    /**
     * @Then :count certificate should be generated
     */
    public function certificateShouldBeGenerated($count)
    {
        \PHPUnit_Framework_Assert::assertCount((int) $count, glob($this->storageDir.'/domains/*/cert.pem'));
    }

    /**
     * @Then the certificate for the domain :domain should contains:
     */
    public function theCertificateForTheDomainShouldContains($domain, PyStringNode $content)
    {
        $certFile = $this->storageDir.'/domains/'.$domain.'/cert.pem';
        $rawData = openssl_x509_parse(file_get_contents($certFile));
        $formatedData = [
            'subject' => $rawData['subject']['CN'],
            'serialNumber' => $rawData['serialNumber'],
            'issuer' => $rawData['issuer']['CN'],
            'selfSigned' => false !== strpos(
                    $rawData['extensions']['authorityKeyIdentifier'],
                    $rawData['extensions']['subjectKeyIdentifier']
                ),
            'sANs' => array_map(
                function ($item) {
                    return explode(':', trim($item), 2)[1];
                },
                explode(',', $rawData['extensions']['subjectAltName'])
            ),
        ];

        $yaml = new Yaml();
        $expected = $yaml->parse($content->getRaw());

        foreach ($expected as $key => $value) {
            PHPUnit_Framework_Assert::assertArrayHasKey($key, $formatedData);
            PHPUnit_Framework_Assert::assertSame($value, $formatedData[$key]);
        }
    }

    /**
     * @Then a certificate exists for the domain :domain
     */
    public function aCertificateExistsForTheDomain($domain)
    {
        \PHPUnit_Framework_Assert::assertCount(1, glob($this->storageDir.'/domains/'.$domain.'/cert.pem'));
    }

    protected function getDefaultConfig()
    {
        return [
            'certificate_authority' => 'boulder',
            'contact_email' => 'test@acmephp.com',
            'default_distinguished_name' => [
                'country' => 'FR',
                'state' => 'France',
                'locality' => 'Paris',
                'organization_name' => 'acme',
                'organization_unit_name' => 'QA',
            ],
            'domains' => [
                'acmephp.com' => null,
            ],
        ];
    }
}
