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

use AcmePhp\Bundle\Acme\Certificate\Parser\ParserInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass in charge of injecting certificate parsers in the certificate repository.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateParserPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $parsers = [];
        foreach ($container->findTaggedServiceIds('acme_php.certificate_parser') as $id => $attributes) {
            $parserDefinition = $container->getDefinition($id);
            $className = $container->getParameterBag()->resolveValue($parserDefinition->getClass());
            $reflection = new \ReflectionClass($className);
            if (!$reflection->implementsInterface(ParserInterface::class)) {
                throw new \InvalidArgumentException(
                    sprintf('The CertificateParser "%s" is not valid', $parserDefinition->getClass())
                );
            }

            $parsers[] = new Reference($id);
        }

        $container->findDefinition('acme_php.certificate.repository')->replaceArgument(2, $parsers);
    }
}
