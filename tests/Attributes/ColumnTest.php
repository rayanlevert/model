<?php

namespace RayanLevert\Model\Tests\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use RayanLevert\Model\Attributes\Column;
use RayanLevert\Model\Columns\Type;

#[CoversClass(Column::class)]
class ColumnTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function constructNoValuesPassed(): void
    {
        $o = new Column(Type::CHAR);
        
        $this->assertSame(Type::CHAR, $o->type);
        $this->assertSame(Column::UNDEFINED, $o->defaultValue);
        $this->assertNull($o->length);
        $this->assertNull($o->name);
        $this->assertFalse($o->nullable);
    }

    #[Test]
    public function constructValuesPassed(): void
    {
        $o = new Column(Type::CHAR, 'name test', "default value", true, 24);
        
        $this->assertSame(Type::CHAR, $o->type);
        $this->assertSame('default value', $o->defaultValue);
        $this->assertSame(24, $o->length);
        $this->assertSame('name test', $o->name);
        $this->assertTrue($o->nullable);
    }
}