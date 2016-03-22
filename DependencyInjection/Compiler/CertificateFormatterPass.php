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

use AcmePhp\Bundle\Acme\Certificate\Formatter\FormatterInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass in charge of injecting certificate formatters in the certificate repository.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateFormatterPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $formatters = [];
        foreach ($container->findTaggedServiceIds('acme_php.certificate_formatter') as $id => $attributes) {
            $formatterDefinition = $container->getDefinition($id);
            $className = $container->getParameterBag()->resolveValue($formatterDefinition->getClass());
            $reflection = new \ReflectionClass($className);
            if (!$reflection->implementsInterface(FormatterInterface::class)) {
                throw new \InvalidArgumentException(
                    sprintf('The CertificateFormatter "%s" is not valid', $formatterDefinition->getClass())
                );
            }

            $formatters[] = new Reference($id);
        }

        $container->findDefinition('acme_php.certificate.repository')->replaceArgument(1, $formatters);
    }
}
