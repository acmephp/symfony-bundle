<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages the bundle configuration.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class AcmePhpExtension extends Extension implements PrependExtensionInterface
{
    const EXTENSION_NAME_MONOLOG = 'monolog';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('acme_php.domains_configurations', (array) $config['domains']);
        $container->setParameter('acme_php.certificate_dir', $config['certificate_dir']);
        $container->setParameter('acme_php.certificate_authority', $config['certificate_authority']);
        $container->setParameter('acme_php.contact_email', $config['contact_email']);
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension(self::EXTENSION_NAME_MONOLOG)) {
            $container->prependExtensionConfig(
                self::EXTENSION_NAME_MONOLOG,
                [
                    'channel' => ['acme_php'],
                ]
            );

            $container->setAlias('acme_php.logger', 'monolog.logger.acme_php');
        } else {
            $container->setAlias('acme_php.logger', 'acme_php.logger.null');
        }
    }
}
