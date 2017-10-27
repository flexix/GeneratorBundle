<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flexix\GeneratorBundle\Manipulator;

use Symfony\Component\HttpKernel\KernelInterface;
use Flexix\GeneratorBundle\Generator\Generator;

/**
 * Changes the PHP code of a Kernel.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExtensionManipulator extends Manipulator {

    protected $className;
    protected $reflected;
    protected $module;
    protected $alias;
    protected $method;

    public function __construct($className, $module, $alias) {
        $this->className = $className;
        $this->module = $module;
        $this->alias = $alias;
        $this->reflected = new \ReflectionClass($className);
    }

    protected function getLoadMethod() {

        if (!$this->method) {

            $this->method = $this->reflected->getMethod('load');
        }

        return $this->method;
    }

    protected function getMethodLines($lines) {
        $method = $this->getLoadMethod();
        return array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
    }

    protected function getLeadingContent($lines) {
        $method = $this->getLoadMethod();
        return array_slice($lines, 0, $method->getStartLine() - 1);
    }

    protected function getEndingContent($lines) {
        $method = $this->getLoadMethod();
        return array_slice($lines, $method->getEndLine()+1, $method->getStartLine() - 1, count($lines) - 1);
    }

    protected function getCodeFragment() {
        
        $load=sprintf('$loader->load(\'services\%s\%s.yml\');', $this->module, $this->alias);
        echo "$load\n";
        return $load;
    }

    protected function removeRightBracket($string) {

        $pos = strrpos('}', $string);

        if ($pos !== false) {
            return substr_replace($string,'', $pos, strlen($string));
        }

    }

    protected function changeMethodBody($lines) {
        $methodLines = $this->getMethodLines($lines);
        $linesNumber = count($methodLines);
        $lastIndex = $linesNumber - 1;
        $methodLines[$lastIndex] = sprintf("\t%s%s\n\t}\n", $this->removeRightBracket($methodLines[$lastIndex]), $this->getCodeFragment());
        return $methodLines;
    }

    /**
     * Adds a bundle at the end of the existing ones.
     *
     * @param string $relativeServicePath The bundle class name
     *
     * @return bool Whether the operation succeeded
     *
     * @throws \RuntimeException If bundle is already defined
     */
    public function addService() {
        if (!$this->getFilename()) {
            return false;
        }

        $filename = $this->getFilename();
        $content = file_get_contents($filename);

        if (!strstr($content,$this->getCodeFragment())) {

            $lines = file($this->getFilename());
            $resultLines = array_merge(
                    $this->getLeadingContent($lines), $this->changeMethodBody($lines), $this->getEndingContent($lines));
            Generator::dump($this->getFilename(), implode('', $resultLines));
        }

        return true;
    }

    public function getFilename() {
        return $this->reflected->getFileName();
    }

}
