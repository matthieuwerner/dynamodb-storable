<?php

namespace Storable\Tests\Interface;

use PHPUnit\Framework\TestCase;
use Storable\Interface\StorableInterface;

/**
 * @internal
 * @coversNothing
 */
class StorageInterfaceTest extends TestCase
{
    public function testInterfaceExists()
    {
        $class = new \ReflectionClass(StorableInterface::class);
        $this->assertTrue($class->isInterface());
    }

    public function testInterfaceGetIdMethos()
    {
        $class = new \ReflectionClass(StorableInterface::class);
        $this->assertTrue($class->hasMethod('getId'));

        $method = $class->getMethod('getId');
        $this->assertEquals('string', $method->getReturnType());
    }
}
