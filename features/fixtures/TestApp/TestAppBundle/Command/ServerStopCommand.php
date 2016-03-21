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
use Symfony\Bundle\FrameworkBundle\Command\ServerStopCommand as BaseServerStopCommand;

class ServerStopCommand extends BaseServerStopCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('address', InputArgument::OPTIONAL, 'Address:port', '127.0.0.1:5002'),
            ))
            ->setName('acmephp:server:stop');
    }
}
