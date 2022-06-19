<?php

namespace Storable\Tests;

use Storable\Interface\StorableInterface;

final class ValueObjectTest implements StorableInterface
{
    private string $id;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
