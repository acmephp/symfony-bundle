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
use AcmePhp\Bundle\Acme\Domain\Loader\ArrayLoader;

class ArrayLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function test load returns instances of DomainConfiguration()
    {
        $dummyConfiguration = [
            'company.com' => [
                'country' => uniqid(),
                'state' => uniqid(),
                'locality' => uniqid(),
                'organization_name' => uniqid(),
                'organization_unit_name' => uniqid(),
                'email_address' => uniqid(),
            ],
            'www.company.com' => [
                'country' => uniqid(),
                'state' => uniqid(),
                'locality' => uniqid(),
                'organization_name' => uniqid(),
                'organization_unit_name' => uniqid(),
                'email_address' => uniqid(),
            ],
        ];

        $service = new ArrayLoader($dummyConfiguration);

        $result = $service->load();

        $this->assertInternalType('array', $result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(DomainConfiguration::class, $result[0]);
        $this->assertInstanceOf(DomainConfiguration::class, $result[1]);
        $this->assertSame('company.com', $result[0]->getDomain());
        $this->assertSame('www.company.com', $result[1]->getDomain());
    }
}
