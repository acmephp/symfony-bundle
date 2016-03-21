<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle;

use AcmePhp\Bundle\DependencyInjection\Compiler\CertificateAuthorityConfigurationPass;
use AcmePhp\Bundle\DependencyInjection\Compiler\CertificateExtractorPass;
use AcmePhp\Bundle\DependencyInjection\Compiler\CertificateFormatterPass;
use AcmePhp\Bundle\DependencyInjection\Compiler\DomainConfigurationLoaderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * AcmePhp Bundle.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class AcmePhpBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DomainConfigurationLoaderPass());
        $container->addCompilerPass(new CertificateAuthorityConfigurationPass());
        $container->addCompilerPass(new CertificateFormatterPass());
        $container->addCompilerPass(new CertificateExtractorPass());
    }
}
