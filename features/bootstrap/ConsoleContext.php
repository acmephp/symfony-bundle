<?php

/*
 * This file is part of the ACME PHP library.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Defines Symfony console features from the specific context.
 *
 * @author Jérémy Derussé <jeremy@derusse.com>
 */
class ConsoleContext implements Context, SnippetAcceptingContext
{
    /**
     * @var Kernel
     */
    private $kernel;

    private $lastRun;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When I run the command :commandName
     */
    public function iRunTheCommand($commandName)
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput(['command' => $commandName]);

        $output = new NullOutput();
        $this->lastRun = $application->run($input, $output);
    }

    /**
     * @Then the command should be exit with code :code
     */
    public function theCommandShouldBeExitWithCode($code)
    {
        \PHPUnit_Framework_Assert::assertSame((int) $code, $this->lastRun);
    }
}
