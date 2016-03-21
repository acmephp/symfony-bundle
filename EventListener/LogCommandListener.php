<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AcmePhp\Bundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listen to Console event to logs errors and abnormal exists.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class LogCommandListener implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::EXCEPTION => 'onConsoleException',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
        ];
    }

    /**
     * Logs exception events for AcmePhp commands.
     *
     * @param ConsoleExceptionEvent $event
     */
    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        if (!$this->isAcmePhpCommand($event->getCommand())) {
            return;
        }

        $this->logger->error('Exception thrown while running command "{command}". Message: "{message}".', [
            'command' => $event->getCommand()->getName(),
            'message' => $event->getException()->getMessage(),
            'exception' => $event->getException(),
        ]);
    }

    /**
     * Log Abnormal exists for AcmPhp commands.
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        if (0 === $exitCode = $event->getExitCode()) {
            return;
        }

        if (!$this->isAcmePhpCommand($event->getCommand())) {
            return;
        }

        $this->logger->error('Command "{command}" exited with code "{exitCode}".', [
            'command' => $event->getCommand()->getName(),
            'exitCode' => $exitCode,
        ]);
    }

    /**
     * Returnrs whether or not the command belongs to the AcmePhp bundle.
     *
     * @param Command $command
     *
     * @return bool
     */
    private function isAcmePhpCommand(Command $command)
    {
        return 0 === strpos(get_class($command), 'AcmePhp\Bundle');
    }
}
