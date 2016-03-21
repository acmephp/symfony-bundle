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
use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Core\Ssl\CSR;
use AcmePhp\Bundle\Acme\Certificate\Extractor\CertificateExtractor;

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
     * @Given a :domain certificate
     */
    public function aCertificate($domain)
    {
        $this->generateCertificate($domain);
    }

    /**
     * @Given a :domain certificate which contains:
     */
    public function aCertificateWhichContains($domain, PyStringNode $rawConfig)
    {
        $yaml = new Yaml();
        $csrConfig = $yaml->parse($rawConfig->getRaw());

        $this->generateCertificate($domain, $csrConfig);
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
        $extractor = new CertificateExtractor();
        $formatedData = $extractor->extract(file_get_contents($certFile));

        $yaml = new Yaml();
        $expected = $yaml->parse($content->getRaw());

        foreach ($expected as $key => $value) {
            PHPUnit_Framework_Assert::assertArrayHasKey($key, $formatedData);
            if (is_array($value) && is_array($formatedData[$key])) {
                sort($value);
                sort($formatedData[$key]);
            }
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
            'certificate_dir' => '%kernel.root_dir%/certs',
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

    private function generateCertificate($domain, array $csrConfig = [])
    {
        $defaultConfig = $this->getDefaultConfig();
        $defaultConfig['default_distinguished_name']['email_address'] = $defaultConfig['contact_email'];
        $csrConfig = $defaultConfig['default_distinguished_name'] + $csrConfig;

        $this->kernel->getContainer()->get('acme_php.certificate.requester')->requestCertificate(
            new DomainConfiguration(
                $domain,
                new CSR(
                    $csrConfig['country'],
                    $csrConfig['state'],
                    $csrConfig['locality'],
                    $csrConfig['organization_name'],
                    $csrConfig['organization_unit_name'],
                    $csrConfig['email_address']
                )
            )
        );
    }
}
