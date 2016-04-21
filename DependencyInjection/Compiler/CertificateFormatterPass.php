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

use AcmePhp\Ssl\Formatter\FormatterInterface;
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
        $repositoryDefinition = $container->findDefinition('acme_php.certificate.repository');

        foreach ($container->findTaggedServiceIds('acme_php.certificate_formatter') as $id => $tags) {
            $formatterDefinition = $container->getDefinition($id);
            $className = $container->getParameterBag()->resolveValue($formatterDefinition->getClass());
            $reflection = new \ReflectionClass($className);
            if (!$reflection->implementsInterface(FormatterInterface::class)) {
                throw new \InvalidArgumentException(
                    sprintf('The CertificateFormatter "%s" is not valid', $formatterDefinition->getClass())
                );
            }

            foreach ($tags as $attributes) {
                if (!isset($attributes['filename'])) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'The the filename for the CertificateFormatter "%s" is not defined',
                            $formatterDefinition->getClass()
                        )
                    );
                }
                $repositoryDefinition->addMethodCall('addFormatter', [$attributes['filename'], new Reference($id)]);
            }
        }
    }
}
