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

use AcmePhp\Bundle\Acme\Certificate\Extractor\ExtractorInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass in charge of injecting certificate extractors in the certificate repository.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateExtractorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $extractors = [];
        foreach ($container->findTaggedServiceIds('acme_php.certificate_extractor') as $id => $attributes) {
            $extractorDefinition = $container->getDefinition($id);
            $className = $container->getParameterBag()->resolveValue($extractorDefinition->getClass());
            $reflection = new \ReflectionClass($className);
            if (!$reflection->implementsInterface(ExtractorInterface::class)) {
                throw new \InvalidArgumentException(
                    sprintf('The CertificateExtractor "%s" is not valid', $extractorDefinition->getClass())
                );
            }

            $extractors[] = new Reference($id);
        }

        $container->findDefinition('acme_php.certificate.repository')->replaceArgument(2, $extractors);
    }
}
