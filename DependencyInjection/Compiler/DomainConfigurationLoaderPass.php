<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\DependencyInjection\Compiler;

use AcmePhp\Bundle\Acme\Domain\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass in charge of registering domain configurators.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class DomainConfigurationLoaderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('acme_php.domains_configurations.loader');
        foreach ($container->findTaggedServiceIds('acme_php.domains_configurations_loader') as $id => $attributes) {
            $loaderDefinition = $container->getDefinition($id);
            $className = $container->getParameterBag()->resolveValue($loaderDefinition->getClass());
            $reflection = new \ReflectionClass($className);
            if (!$reflection->implementsInterface(LoaderInterface::class)) {
                throw new \InvalidArgumentException(sprintf('The DomainConfigurationLoader "%s" is not valid', $loaderDefinition->getClass()));
            }

            $definition->addMethodCall('addLoader', [$loaderDefinition]);
        }
    }
}
