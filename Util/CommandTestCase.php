<?php

namespace Flexix\GeneratorBundle\Util;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Bundle\FrameworkBundle\Client;

abstract class CommandTestCase extends WebTestCase
{
    /**
     * Runs a command and returns it output
     */
    public static function runCommand($kernel, $command)
    {
        
        
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $fp = tmpfile();
        $input = new StringInput($command);
        $output = new StreamOutput($fp);

        $application->run($input, $output);

        fseek($fp, 0);
        $output = '';
        while (!feof($fp)) {
            $output = fread($fp, 4096);
        }
        fclose($fp);

        return $output;
    }
}