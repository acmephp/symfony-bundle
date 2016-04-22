<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\Acme\CertificateAuthority;

use AcmePhp\Bundle\Acme\CertificateAuthority\ClientFactory;
use AcmePhp\Bundle\Acme\CertificateAuthority\Configuration\CertificateAuthorityConfigurationInterface;
use AcmePhp\Core\AcmeClient;
use AcmePhp\Core\Http\Base64SafeEncoder;
use AcmePhp\Core\Http\ServerErrorHandler;
use AcmePhp\Ssl\KeyPair;
use AcmePhp\Ssl\Parser\KeyParser;
use AcmePhp\Ssl\Signer\DataSigner;
use GuzzleHttp\ClientInterface;

class ClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClientFactory */
    private $service;

    /** @var CertificateAuthorityConfigurationInterface */
    private $mockCA;

    /** @var ClientInterface */
    private $dummyHttpClient;

    /** @var Base64SafeEncoder */
    private $dummyBaseSafeEncoder;

    /** @var KeyParser */
    private $dummyKeyParser;

    /** @var DataSigner */
    private $dummyDataSigner;

    /** @var ServerErrorHandler */
    private $dummyErrorHandler;

    public function setUp()
    {
        parent::setUp();

        $this->mockCA = $this->prophesize(CertificateAuthorityConfigurationInterface::class);
        $this->dummyHttpClient = $this->prophesize(ClientInterface::class)->reveal();
        $this->dummyBaseSafeEncoder = $this->prophesize(Base64SafeEncoder::class)->reveal();
        $this->dummyKeyParser = $this->prophesize(KeyParser::class)->reveal();
        $this->dummyDataSigner = $this->prophesize(DataSigner::class)->reveal();
        $this->dummyErrorHandler = $this->prophesize(ServerErrorHandler::class)->reveal();

        $this->service = new ClientFactory(
            $this->mockCA->reveal(),
            $this->dummyHttpClient,
            $this->dummyBaseSafeEncoder,
            $this->dummyKeyParser,
            $this->dummyDataSigner,
            $this->dummyErrorHandler
        );
    }

    public function test createAcmeClient use CA configuration()
    {
        $keyPair = $this->prophesize(KeyPair::class)->reveal();
        $this->mockCA->getDirectoryUri()->shouldBeCalled();
        $this->mockCA->getAgreement()->shouldNotBeCalled();

        $client = $this->service->createAcmeClient($keyPair);

        $this->assertInstanceOf(AcmeClient::class, $client);
    }
}
