<?php

namespace Storable\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Storable\Exception\StorageException;
use Storable\Interface\StorableInterface;

class StorageExceptionTest extends TestCase
{
    public function testException()
    {
        $this->expectException(StorageException::class);

        throw new StorageException('hey!');
    }
}
