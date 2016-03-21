<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\Domain\Loader;

use AcmePhp\Bundle\Acme\Domain\DomainConfiguration;
use AcmePhp\Bundle\Acme\Domain\Loader\LoaderChain;
use AcmePhp\Bundle\Acme\Domain\Loader\LoaderInterface;

class LoaderChainTest extends \PHPUnit_Framework_TestCase
{
    public function test load returns merged configurations from loaders()
    {
        $mockLoader1 = $this->prophesize(LoaderInterface::class);
        $mockLoader2 = $this->prophesize(LoaderInterface::class);

        $dummyConfiguration1 = $this->prophesize(DomainConfiguration::class)->reveal();
        $dummyConfiguration2 = $this->prophesize(DomainConfiguration::class)->reveal();
        $dummyConfiguration3 = $this->prophesize(DomainConfiguration::class)->reveal();

        $mockLoader1->load()->willReturn([$dummyConfiguration1, $dummyConfiguration2]);
        $mockLoader2->load()->willReturn([$dummyConfiguration3]);

        $service = new LoaderChain();
        $service->addLoader($mockLoader1->reveal());
        $service->addLoader($mockLoader2->reveal());

        $result = $service->load();

        $this->assertSame([$dummyConfiguration1, $dummyConfiguration2, $dummyConfiguration3], $result);
    }
}
