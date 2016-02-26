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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass in charge of selecting the configured certificate authority.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class CertificateAuthorityConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $certificateAuthority = $container->getParameter('acme_php.certificate_authority');

        foreach ($container->findTaggedServiceIds('acme_php.certificate_authority') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new \InvalidArgumentException(sprintf('Missing alias for "acme_php.certificate_authority" service "%s"', $id));
                }

                if ($certificateAuthority === $attribute['alias']) {
                    $container->setAlias('acme_php.core.certificate_authority', $id);

                    return;
                }
            }
        }

        throw new \InvalidArgumentException(sprintf('Unable to find the "%s" Certificate Authority', $certificateAuthority));
    }
}
