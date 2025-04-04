<?php

namespace RayanLevert\Model\Tests\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RayanLevert\Model\Attributes\PrimaryKey;
use ReflectionProperty;

#[CoversClass(PrimaryKey::class)]
class PrimaryKeyTest extends TestCase
{
    #[Test]
    public function primaryKeyAttributeOnProperty(): void
    {
        $testClass = new class {
            #[PrimaryKey]
            public int $id;
        };

        $attributes = new ReflectionProperty($testClass, 'id')->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertInstanceOf(PrimaryKey::class, $attributes[0]->newInstance());
    }
}
