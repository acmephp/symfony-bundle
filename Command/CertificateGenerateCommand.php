<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Command;

use AcmePhp\Ssl\DistinguishedName;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to generate certificates.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateGenerateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('acmephp:generate')
            ->setDescription('Request a certificate to the Certificate Authority');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = $this->getContainer()->get('acme_php.domains_configurations.loader');
        $requester = $this->getContainer()->get('acme_php.certificate.requester');

        /* @var DistinguishedName $distinguishedName */
        $hasError = false;
        foreach ($loader->load() as $distinguishedName) {
            try {
                $requester->requestCertificate($distinguishedName);
                $output->writeln(
                    sprintf(
                        '<info>Certificate for domain <comment>%s</comment> generated.',
                        $distinguishedName->getCommonName()
                    )
                );
            } catch (\Exception $e) {
                $this->getContainer()->get('acme_php.logger')->error(
                    'Fail to generate certificate for domain "{domain}"',
                    ['domain' => $distinguishedName->getCommonName(), 'exception' => $e]
                );
                $output->writeln(
                    sprintf(
                        '<error>Fail to generate certificate for domain <info>%s</info>:
<comment>%s</comment>.',
                        $distinguishedName->getCommonName(),
                        $e->getMessage()
                    )
                );
                $hasError = true;
            }
        }

        return $hasError ? 1 : 0;
    }
}
