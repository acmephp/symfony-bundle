<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TestAppBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\FrameworkBundle\Command\ServerRunCommand as BaseServerRunCommand;

class ServerRunCommand extends BaseServerRunCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(
                array(
                    new InputArgument('address', InputArgument::OPTIONAL, 'Address:port', '127.0.0.1:5002'),
                    new InputOption('docroot', 'd', InputOption::VALUE_REQUIRED, 'Document root', __DIR__),
                    new InputOption('router', 'r', InputOption::VALUE_REQUIRED, 'Path to custom router script', __DIR__.'/../../config/router.php'),
                )
            )
            ->setName('acmephp:server:run');
    }
}
