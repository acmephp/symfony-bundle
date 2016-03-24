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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from the app/config files.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('acme_php');

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($conf) {
                    return isset($conf['default_distinguished_name']) && empty($conf['default_distinguished_name']['email_address']);
                })
                ->then(function ($conf) {
                    $conf['default_distinguished_name']['email_address'] = $conf['contact_email'];

                    return $conf;
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($conf) {
                    return isset($conf['default_distinguished_name']);
                })
                ->then(function ($conf) {
                    foreach ($conf['domains'] as &$domainConf) {
                        $domainConf = array_replace($conf['default_distinguished_name'], (array) $domainConf);
                    }

                    return $conf;
                })
            ->end()
            ->children()
                ->scalarNode('contact_email')
                    ->info('Email Address.')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($item) {
                            return !filter_var($item, FILTER_VALIDATE_EMAIL);
                        })
                        ->thenInvalid('The email "%s" is not valid.')
                    ->end()
                ->end()
                ->scalarNode('certificate_dir')
                    ->info('Certification location directory.')
                    ->cannotBeEmpty()
                    ->defaultValue('~/.acmephp')
                ->end()
                ->scalarNode('certificate_authority')
                    ->info('Name of the certificate authority.')
                    ->cannotBeEmpty()
                    ->defaultValue('letsencrypt')
                ->end()
                ->append($this->createDefaultDistinguishedNameSection())
                ->append($this->createDomainDistinguishedNameSection())
            ->end();

        return $treeBuilder;
    }

    private function createDefaultDistinguishedNameSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('default_distinguished_name');

        $rootNode
            ->info('Default Distinguished Name (or a DN) informations.')
            ->addDefaultsIfNotSet();

        $this->addDistinguishedNameSection($rootNode);

        return $rootNode;
    }

    private function createDomainDistinguishedNameSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('domains');

        $domainNode = $rootNode
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->fixXmlConfig('domain')
            ->normalizeKeys(false)
            ->prototype('array')
            ->children()
                ->arrayNode('subject_alternative_names')
                    ->info('Alternative subject names.')
                    ->requiresAtLeastOneElement()
                    ->fixXmlConfig('subject_alternative_name')
                    ->normalizeKeys(false)
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        $this->addDistinguishedNameSection($domainNode);

        $domainNode
            ->end();

        return $rootNode;
    }

    private function addDistinguishedNameSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('country')
                    ->info('Country Name (2 letter code).')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($item) {
                            return 2 !== strlen($item);
                        })
                        ->thenInvalid('The country code "%s" is not valid.')
                    ->end()
                ->end()
                ->scalarNode('state')
                    ->info('State or Province Name (full name).')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('locality')
                    ->info('Locality Name (eg, city).')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('organization_name')
                    ->info('Organization Name (eg, company).')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('organization_unit_name')
                    ->info('Organizational Unit Name (eg, section).')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('email_address')
                    ->info('Email Address (default: contact_email).')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->ifTrue(function ($item) {
                            return !filter_var($item, FILTER_VALIDATE_EMAIL);
                        })
                        ->thenInvalid('The email "%s" is not valid.')
                    ->end()
                ->end()
            ->end();
    }
}
