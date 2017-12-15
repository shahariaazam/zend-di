<?php
/**
 * @see       https://github.com/zendframework/zend-di for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-di/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Di\Resolver;

use PHPUnit\Framework\TestCase;
use stdClass;
use Zend\Di\Exception;
use Zend\Di\Resolver\ValueInjection;
use ZendTest\Di\TestAsset;

/**
 * @coversDefaultClass Zend\Di\Resolver\ValueInjection
 */
class ValueInjectionTest extends TestCase
{
    private $streamFixture = null;

    protected function setUp()
    {
        parent::setUp();

        if (! $this->streamFixture) {
            $this->streamFixture = fopen('php://temp', 'w+');
        }
    }

    protected function tearDown()
    {
        if ($this->streamFixture) {
            fclose($this->streamFixture);
            $this->streamFixture = null;
        }

        parent::tearDown();
    }

    public function provideConstructionValues()
    {
        return [
            'string' => ['Hello World'],
            'bool'   => [true],
            'int'    => [7364234],
            'object' => [new stdClass()],
        ];
    }

    /**
     * @dataProvider provideConstructionValues
     */
    public function testSetStateConstructsInstance($value)
    {
        $result = ValueInjection::__set_state(['value' => $value]);
        $this->assertInstanceOf(ValueInjection::class, $result);
        $this->assertSame($value, $result->getValue());
    }

    public function provideExportableValues()
    {
        return [
            'string'       => ['Testvalue'],
            'int'          => [124342],
            'randomString' => [uniqid()],
            'time'         => [time()],
            'true'         => [true],
            'false'        => [false],
            'null'         => [null],
            'float'        => [microtime(true)],
            'object'       => [new TestAsset\Resolver\ExportableValue()],
        ];
    }

    public function provideUnexportableItems()
    {
        if (! $this->streamFixture) {
            $this->streamFixture = fopen('php://temp', 'w+');
        }

        return [
            'stream'          => [$this->streamFixture],
            'noSetState'      => [new TestAsset\Resolver\UnexportableValue1()],
            'privateSetState' => [new TestAsset\Resolver\UnexportableValue2()],
        ];
    }

    /**
     * @dataProvider provideUnexportableItems
     */
    public function testExportThrowsExceptionForUnexportable($value)
    {
        $instance = new ValueInjection($value);

        $this->expectException(Exception\RuntimeException::class);
        $instance->export();
    }

    /**
     * @dataProvider provideUnexportableItems
     */
    public function testIsExportableReturnsFalseForUnexportable($value)
    {
        $instance = new ValueInjection($value);
        $this->assertFalse($instance->isExportable());
    }

    /**
     * @dataProvider provideExportableValues
     */
    public function testIsExportableReturnsTrueForExportableValues($value)
    {
        $instance = new ValueInjection($value);
        $this->assertTrue($instance->isExportable());
    }

    /**
     * @dataProvider provideExportableValues
     */
    public function testExportWithExportableValues($value)
    {
        $instance = new ValueInjection($value);
        $result = $instance->export();

        $this->assertInternalType('string', $result, 'Export is expected to return a string value');
        $this->assertNotEquals('', $result, 'The exported value must not be empty');
    }
}