<?php
namespace TYPO3\Flow\Tests\Unit\Object\Proxy;

/*
 * This file is part of the TYPO3.Flow package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 *
 */
class ProxyMethodTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function buildMethodDocumentationAddsAllExpectedAnnotations()
    {
        $validateFoo1 = new \TYPO3\Flow\Annotations\Validate(array('value' => 'foo1', 'type' => 'bar1'));
        $validateFoo2 = new \TYPO3\Flow\Annotations\Validate(array('value' => 'foo2', 'type' => 'bar2'));

        $mockReflectionService = $this->getMock(\TYPO3\Flow\Reflection\ReflectionService::class, array(), array(), '', false);
        $mockReflectionService->expects($this->any())->method('hasMethod')->will($this->returnValue(true));
        $mockReflectionService->expects($this->any())->method('getIgnoredTags')->will($this->returnValue(array('return')));
        $mockReflectionService->expects($this->any())->method('getMethodTagsValues')->with('My\Class\Name', 'myMethod')->will($this->returnValue(array(
            'param' => array('string $name')
        )));
        $mockReflectionService->expects($this->any())->method('getMethodAnnotations')->with('My\Class\Name', 'myMethod')->will($this->returnValue(array(
            $validateFoo1,
            $validateFoo2,
            new \TYPO3\Flow\Annotations\SkipCsrfProtection(array())
        )));

        $mockProxyMethod = $this->getAccessibleMock(\TYPO3\Flow\Object\Proxy\ProxyMethod::class, array('dummy'), array(), '', false);
        $mockProxyMethod->injectReflectionService($mockReflectionService);
        $methodDocumentation = $mockProxyMethod->_call('buildMethodDocumentation', 'My\Class\Name', 'myMethod');

        $expected =
            '    /**' . chr(10) .
            '     * Autogenerated Proxy Method' . chr(10) .
            '     * @param string $name' . chr(10) .
            '     * @\TYPO3\Flow\Annotations\Validate(type="bar1", argumentName="foo1")' . chr(10) .
            '     * @\TYPO3\Flow\Annotations\Validate(type="bar2", argumentName="foo2")' . chr(10) .
            '     * @\TYPO3\Flow\Annotations\SkipCsrfProtection' . chr(10) .
            '     */' . chr(10);
        $this->assertEquals($expected, $methodDocumentation);
    }

    /**
     * @test
     */
    public function buildMethodParametersCodeRendersParametersCodeWithCorrectTypeHintsAndDefaultValues()
    {
        $className = 'TestClass' . md5(uniqid(mt_rand(), true));
        eval('
            /**
             * @param string $arg1 Arg1
             */
            class ' . $className . ' {
                public function foo($arg1, array $arg2, \ArrayObject $arg3, $arg4= "foo", $arg5 = TRUE, array $arg6 = array(TRUE, \'foo\' => \'bar\', NULL, 3 => 1, 2.3)) {}
            }
        ');
        $methodParameters = array(
            'arg1' => array(
                'position' => 0,
                'byReference' => false,
                'array' => false,
                'optional' => false,
                'allowsNull' => true,
                'class' => null
            ),
            'arg2' => array(
                'position' => 1,
                'byReference' => false,
                'array' => true,
                'optional' => false,
                'allowsNull' => true,
                'class' => null
            ),
            'arg3' => array(
                'position' => 2,
                'byReference' => false,
                'array' => false,
                'optional' => false,
                'allowsNull' => true,
                'class' => 'ArrayObject'
            ),
            'arg4' => array(
                'position' => 3,
                'byReference' => false,
                'array' => false,
                'optional' => true,
                'allowsNull' => true,
                'class' => null,
                'defaultValue' => 'foo'
            ),
            'arg5' => array(
                'position' => 4,
                'byReference' => false,
                'array' => false,
                'optional' => true,
                'allowsNull' => true,
                'class' => null,
                'defaultValue' => true
            ),
            'arg6' => array(
                'position' => 5,
                'byReference' => false,
                'array' => true,
                'optional' => true,
                'allowsNull' => true,
                'class' => null,
                'defaultValue' => array(0 => true, 'foo' => 'bar', 1 => null, 3 => 1, 4 => 2.3)
            ),
        );

        $mockReflectionService = $this->getMock(\TYPO3\Flow\Reflection\ReflectionService::class);
        $mockReflectionService->expects($this->atLeastOnce())->method('getMethodParameters')->will($this->returnValue($methodParameters));

        $expectedCode = '$arg1, array $arg2, \ArrayObject $arg3, $arg4 = \'foo\', $arg5 = TRUE, array $arg6 = array(0 => TRUE, \'foo\' => \'bar\', 1 => NULL, 3 => 1, 4 => 2.3)';

        $builder = $this->getMock(\TYPO3\Flow\Object\Proxy\ProxyMethod::class, array('dummy'), array(), '', false);
        $builder->injectReflectionService($mockReflectionService);

        $actualCode = $builder->buildMethodParametersCode($className, 'foo', true);
        $this->assertSame($expectedCode, $actualCode);
    }

    /**
     * @test
     */
    public function buildMethodParametersCodeOmitsTypeHintsAndDefaultValuesIfToldSo()
    {
        $className = 'TestClass' . md5(uniqid(mt_rand(), true));
        eval('
            class ' . $className . ' {
                public function foo($arg1, array $arg2, \ArrayObject $arg3, $arg4= "foo", $arg5 = TRUE) {}
            }
        ');

        $mockReflectionService = $this->getMock(\TYPO3\Flow\Reflection\ReflectionService::class);
        $mockReflectionService->expects($this->atLeastOnce())->method('getMethodParameters')->will($this->returnValue(array(
            'arg1' => array(),
            'arg2' => array(),
            'arg3' => array(),
            'arg4' => array(),
            'arg5' => array()
        )));

        $expectedCode = '$arg1, $arg2, $arg3, $arg4, $arg5';

        $builder = $this->getMock(\TYPO3\Flow\Object\Proxy\ProxyMethod::class, array('dummy'), array(), '', false);
        $builder->injectReflectionService($mockReflectionService);

        $actualCode = $builder->buildMethodParametersCode($className, 'foo', false);
        $this->assertSame($expectedCode, $actualCode);
    }

    /**
     * @test
     */
    public function buildMethodParametersCodeReturnsAnEmptyStringIfTheClassNameIsNULL()
    {
        $builder = $this->getMock(\TYPO3\Flow\Object\Proxy\ProxyMethod::class, array('dummy'), array(), '', false);

        $actualCode = $builder->buildMethodParametersCode(null, 'foo', true);
        $this->assertSame('', $actualCode);
    }
}
