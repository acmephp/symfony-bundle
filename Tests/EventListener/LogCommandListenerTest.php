<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\Tests\EventListener;

use AcmePhp\Bundle\Command\CertificateGenerateCommand;
use AcmePhp\Bundle\EventListener\LogCommandListener;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class LogCommandListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var LogCommandListener */
    private $service;

    /** @var LoggerInterface */
    private $mockLogger;

    public function setUp()
    {
        parent::setUp();

        $this->mockLogger = $this->prophesize(LoggerInterface::class);

        $this->service = new LogCommandListener(
            $this->mockLogger->reveal()
        );
    }

    public function test getSubscribedEvents listens to command events()
    {
        $result = $this->service->getSubscribedEvents();
        $this->assertArrayHasKey('console.exception', $result);
        $this->assertArrayHasKey('console.terminate', $result);
    }

    public function test onConsoleException logs the triggered exception()
    {
        $dummyCommand = new CertificateGenerateCommand();
        $dummyException = new \Exception();
        $mockEvent = $this->prophesize(ConsoleExceptionEvent::class);
        $mockEvent->getCommand()->willReturn($dummyCommand);
        $mockEvent->getException()->willReturn($dummyException);

        $this->mockLogger->error('Exception thrown while running command "{command}". Message: "{message}".', Argument::any())->shouldBeCalled();

        $this->service->onConsoleException($mockEvent->reveal());
    }

    public function test onConsoleException logs only acmePhp exception()
    {
        $dummyCommand = new Command('test');
        $dummyException = new \Exception();
        $mockEvent = $this->prophesize(ConsoleExceptionEvent::class);
        $mockEvent->getCommand()->willReturn($dummyCommand);
        $mockEvent->getException()->willReturn($dummyException);

        $this->mockLogger->error()->shouldNotBeCalled();

        $this->service->onConsoleException($mockEvent->reveal());
    }

    public function test onConsoleTerminate logs errors on exit()
    {
        $dummyCommand = new CertificateGenerateCommand();
        $mockEvent = $this->prophesize(ConsoleTerminateEvent::class);
        $mockEvent->getCommand()->willReturn($dummyCommand);
        $mockEvent->getExitCode()->willReturn(1);

        $this->mockLogger->error()->shouldNotBeCalled();

        $this->mockLogger->error('Command "{command}" exited with code "{exitCode}".', Argument::any())->shouldBeCalled();

        $this->service->onConsoleTerminate($mockEvent->reveal());
    }

    public function test onConsoleTerminate logs only error exit()
    {
        $dummyCommand = new CertificateGenerateCommand();
        $mockEvent = $this->prophesize(ConsoleTerminateEvent::class);
        $mockEvent->getCommand()->willReturn($dummyCommand);
        $mockEvent->getExitCode()->willReturn(0);

        $this->mockLogger->error()->shouldNotBeCalled();

        $this->service->onConsoleTerminate($mockEvent->reveal());
    }

    public function test onConsoleTerminate logs only acmePhp exception()
    {
        $dummyCommand = new Command('test');
        $mockEvent = $this->prophesize(ConsoleTerminateEvent::class);
        $mockEvent->getCommand()->willReturn($dummyCommand);
        $mockEvent->getExitCode()->willReturn(1);

        $this->mockLogger->error()->shouldNotBeCalled();

        $this->service->onConsoleTerminate($mockEvent->reveal());
    }
}
