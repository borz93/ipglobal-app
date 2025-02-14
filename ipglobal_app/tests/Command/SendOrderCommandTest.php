<?php

namespace App\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SendOrderCommandTest extends KernelTestCase
{
    public function testCommandSendsOrderToQueue()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:send-order');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['product-id' => 1]);

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Successfully sent order', $commandTester->getDisplay());
    }

    public function testInvalidProductIdFails()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('app:send-order');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['product-id' => 'invalid']);

        $this->assertEquals(1, $commandTester->getStatusCode());
        $this->assertStringContainsString('Invalid product ID', $commandTester->getDisplay());
    }
}